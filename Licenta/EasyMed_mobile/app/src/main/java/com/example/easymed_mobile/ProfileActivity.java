package com.example.easymed_mobile;

import android.os.Bundle;
import androidx.appcompat.app.AppCompatActivity;
import android.content.Intent;
import android.widget.EditText;
import android.widget.Spinner;
import android.widget.ArrayAdapter;
import android.widget.Toast;
import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;
import android.view.View;
import okhttp3.MediaType;
import okhttp3.RequestBody;
import org.json.JSONObject;
import com.android.volley.Request;
import com.android.volley.toolbox.StringRequest;
import com.android.volley.toolbox.Volley;
import com.android.volley.VolleyError;
import android.widget.Button;
import android.widget.TextView;
import android.util.Log;

/**
 * Activitate pentru afișarea și editarea profilului pacientului în EasyMed
 *
 * Această activitate permite vizualizarea și actualizarea datelor personale ale pacientului:
 * - Nume, prenume, email, CNP, sex, data nașterii, adresă, grupă sanguină
 * - Afișează și calculează vârsta pe baza datei de naștere
 * - Permite editarea și salvarea profilului
 * - Oferă funcționalitate de logout
 *
 * <p>Folosește API-ul EasyMed pentru a încărca și salva datele pacientului.</p>
 *
 * @author EasyMed Mobile Team
 * @version 1.0
 * @since 2024
 */
public class ProfileActivity extends AppCompatActivity {
    private EditText editNume, editPrenume, editEmail, editCnp, editSex, editDataNasterii, editVarsta, editAdresa;
    private Spinner spinnerGrupaSanguina;
    private static final String[] GRUPE_SANGUINE = {"Selecteaza grupa sanguina", "O(I)+", "O(I)-", "A(II)+", "A(II)-", "B(III)+", "B(III)-", "AB(IV)+", "AB(IV)-"};
    private Button btnEditProfile, btnSaveProfile;
    private boolean isEditMode = false;
    private int patientId;

    /**
     * Metodă apelată la crearea activității
     *
     * Inițializează interfața, preia datele pacientului și configurează listener-ele pentru editare și logout.
     *
     * @param savedInstanceState Starea salvată a activității (poate fi null)
     */
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_profile);

        // Find views
        editNume = findViewById(R.id.edit_nume);
        editPrenume = findViewById(R.id.edit_prenume);
        editEmail = findViewById(R.id.edit_email);
        editCnp = findViewById(R.id.edit_cnp);
        editSex = findViewById(R.id.edit_sex);
        editDataNasterii = findViewById(R.id.edit_data_nasterii);
        editVarsta = findViewById(R.id.edit_varsta);
        editAdresa = findViewById(R.id.edit_adresa);
        spinnerGrupaSanguina = findViewById(R.id.spinner_grupa_sanguina);
        btnEditProfile = findViewById(R.id.btn_edit_profile);
        btnSaveProfile = findViewById(R.id.btn_save_profile);

        setEditMode(false);

        btnEditProfile.setOnClickListener(v -> setEditMode(true));
        btnSaveProfile.setOnClickListener(v -> saveProfile(patientId));

        // Set up spinner
        ArrayAdapter<String> adapter = new ArrayAdapter<>(
            this,
            R.layout.spinner_item_dark, // custom item layout
            GRUPE_SANGUINE
        );
        adapter.setDropDownViewResource(R.layout.spinner_dropdown_item_dark);
        spinnerGrupaSanguina.setAdapter(adapter);

        // Get patient_id from intent
        Intent intent = getIntent();
        patientId = intent.getIntExtra("patient_id", -1);
        // Removed patient_id Toast
        android.util.Log.d("ProfileActivity", "patient_id: " + patientId);
        if (patientId == -1) {
            Toast.makeText(this, "Eroare: patient_id lipsă!", Toast.LENGTH_LONG).show();
            return;
        }

        // Back button logic
        TextView backButton = findViewById(R.id.back_button);
        if (backButton != null) {
            backButton.setOnClickListener(v -> {
                Log.d("ProfileActivity", "Back button clicked");
                finish();
            });
        } else {
            Log.e("ProfileActivity", "Back button not found!");
        }

        Button logoutButton = findViewById(R.id.logout_button);
        logoutButton.setOnClickListener(v -> {
            // Clear user session (SharedPreferences)
            android.content.SharedPreferences prefs = getSharedPreferences("easymed_prefs", MODE_PRIVATE);
            prefs.edit().clear().apply();
            
            // Show success toast
            Toast.makeText(ProfileActivity.this, "V-ați deconectat cu succes", Toast.LENGTH_LONG).show();
            
            // Redirect to MainActivity
            Intent logoutIntent = new Intent(ProfileActivity.this, MainActivity.class);
            logoutIntent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);
            startActivity(logoutIntent);
            finish();
        });

        // Fetch profile
        Log.d("ProfileActivity", "Fetching profile for patient_id: " + patientId);
        ApiService apiService = ApiClient.getClient().create(ApiService.class);
        apiService.getPatientProfile(patientId).enqueue(new Callback<Patient>() {
            @Override
            public void onResponse(Call<Patient> call, Response<Patient> response) {
                Log.d("ProfileActivity", "API response received. Success: " + response.isSuccessful() + 
                      ", Code: " + response.code() + ", Body: " + (response.body() != null ? "not null" : "null"));
                
                if (response.isSuccessful() && response.body() != null) {
                    Patient patient = response.body();
                    Log.d("ProfileActivity", "Patient data received: " + patient.getNume() + " " + patient.getPrenume());
                    editNume.setText(patient.getNume());
                    editPrenume.setText(patient.getPrenume());
                    editEmail.setText(patient.getEmail());
                    editCnp.setText(patient.getCnp());
                    editSex.setText(patient.getSex().equals("M") ? "Masculin" : "Feminin");
                    editDataNasterii.setText(patient.getDataNasterii());
                    editAdresa.setText(patient.getAdresa());
                    // Calculate age
                    String birthDate = patient.getDataNasterii();
                    int age = calculateAge(birthDate);
                    editVarsta.setText(age > 0 ? age + " ani" : "-");
                    // Set spinner
                    String grupaSanguina = patient.getGrupaSanguina();
                    if (grupaSanguina == null || grupaSanguina.isEmpty()) {
                        spinnerGrupaSanguina.setSelection(0);
                    } else {
                        int spinnerPos = adapter.getPosition(grupaSanguina);
                        if (spinnerPos >= 0) spinnerGrupaSanguina.setSelection(spinnerPos);
                        else spinnerGrupaSanguina.setSelection(0);
                    }
                } else {
                    // Try to read error body for a message
                    String errorMsg = "Eroare la încărcarea profilului";
                    try {
                        if (response.errorBody() != null) {
                            String errorJson = response.errorBody().string();
                            if (errorJson.contains("error")) {
                                errorMsg = new org.json.JSONObject(errorJson).getString("error");
                            }
                        }
                    } catch (Exception ignored) {}
                    Toast.makeText(ProfileActivity.this, errorMsg, Toast.LENGTH_LONG).show();
                }
            }
            @Override
            public void onFailure(Call<Patient> call, Throwable t) {
                Log.e("ProfileActivity", "API call failed", t);
                Toast.makeText(ProfileActivity.this, "Eroare rețea: " + t.getMessage(), Toast.LENGTH_LONG).show();
            }
        });
    }

    /**
     * Calculează vârsta pacientului pe baza datei de naștere
     *
     * @param birthDate Data nașterii în format yyyy-MM-dd
     * @return Vârsta ca întreg sau -1 dacă data nu este validă
     */
    private int calculateAge(String birthDate) {
        // birthDate format: yyyy-MM-dd
        try {
            String[] parts = birthDate.split("-");
            int year = Integer.parseInt(parts[0]);
            int month = Integer.parseInt(parts[1]);
            int day = Integer.parseInt(parts[2]);
            java.util.Calendar dob = java.util.Calendar.getInstance();
            dob.set(year, month - 1, day);
            java.util.Calendar today = java.util.Calendar.getInstance();
            int age = today.get(java.util.Calendar.YEAR) - dob.get(java.util.Calendar.YEAR);
            if (today.get(java.util.Calendar.DAY_OF_YEAR) < dob.get(java.util.Calendar.DAY_OF_YEAR)) {
                age--;
            }
            return age;
        } catch (Exception e) {
            return -1;
        }
    }

    /**
     * Activează sau dezactivează modul de editare pentru profil
     *
     * @param enabled true pentru activare editare, false pentru vizualizare
     */
    private void setEditMode(boolean enabled) {
        isEditMode = enabled;
        editNume.setEnabled(enabled);
        editPrenume.setEnabled(enabled);
        editEmail.setEnabled(enabled);
        editAdresa.setEnabled(enabled);
        spinnerGrupaSanguina.setEnabled(enabled);
        btnEditProfile.setVisibility(enabled ? View.GONE : View.VISIBLE);
        btnSaveProfile.setVisibility(enabled ? View.VISIBLE : View.GONE);
    }

    /**
     * Salvează modificările profilului pacientului
     *
     * Trimite datele modificate către server și actualizează UI-ul la succes.
     *
     * @param patientId ID-ul pacientului
     */
    private void saveProfile(int patientId) {
        // Collect data
        String nume = editNume.getText().toString().trim();
        String prenume = editPrenume.getText().toString().trim();
        String email = editEmail.getText().toString().trim();
        String adresa = editAdresa.getText().toString().trim();
        String grupaSanguina = spinnerGrupaSanguina.getSelectedItem().toString();
        String cnp = editCnp.getText().toString().trim();
        String dataNasterii = editDataNasterii.getText().toString().trim();
        String sex = editSex.getText().toString().trim();

        // Simple validation
        if (nume.isEmpty() || prenume.isEmpty() || email.isEmpty()) {
            Toast.makeText(this, "Completați toate câmpurile obligatorii!", Toast.LENGTH_SHORT).show();
            return;
        }

        // Send update to backend (adjust URL as needed)
        String url = "http://10.0.2.2/EasyMed/api/update_patient_profile.php";
        StringRequest postRequest = new StringRequest(Request.Method.POST, url,
            response -> {
                Toast.makeText(this, "Profil actualizat cu succes!", Toast.LENGTH_LONG).show();
                setEditMode(false);
            },
            error -> {
                Toast.makeText(this, "Eroare la actualizarea profilului!", Toast.LENGTH_LONG).show();
            }
        ) {
            @Override
            protected java.util.Map<String, String> getParams() {
                java.util.Map<String, String> params = new java.util.HashMap<>();
                params.put("patient_id", String.valueOf(patientId));
                params.put("nume", nume);
                params.put("prenume", prenume);
                params.put("email", email);
                params.put("adresa", adresa);
                params.put("grupa_sanguina", grupaSanguina);
                params.put("cnp", cnp);
                params.put("data_nasterii", dataNasterii);
                params.put("sex", sex);
                return params;
            }
        };
        Volley.newRequestQueue(this).add(postRequest);
    }
} 
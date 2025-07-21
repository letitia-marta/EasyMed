package com.example.easymed_mobile;

import android.content.Intent;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.*;
import androidx.appcompat.app.AppCompatActivity;

import java.util.HashMap;
import java.util.Map;
import java.util.regex.Pattern;
import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.Response;
import com.android.volley.VolleyError;
import com.android.volley.toolbox.StringRequest;
import com.android.volley.toolbox.Volley;

import org.json.JSONException;
import org.json.JSONObject;

/**
 * Activitate pentru înregistrarea utilizatorilor noi în aplicația EasyMed
 * 
 * Această activitate gestionează procesul complet de înregistrare pentru pacienți:
 * - Afișează formularul de înregistrare cu toate câmpurile necesare
 * - Validează datele introduse (email, CNP, parolă, etc.)
 * - Extrage automat informații din CNP (sex, data nașterii)
 * - Comunică cu serverul pentru înregistrarea utilizatorului
 * - Gestionează erorile de validare și de rețea
 * - Redirecționează către login după înregistrare reușită
 * 
 * <p>Activitatea folosește Volley pentru cererile HTTP și include
 * validări complexe pentru CNP și email.</p>
 * 
 * @author EasyMed Mobile Team
 * @version 1.0
 * @since 2024
 */
public class RegisterActivity extends AppCompatActivity {

    // ==================== CÂMPURI PRIVATE ====================
    
    /**
     * Câmpul pentru introducerea numelui de familie
     */
    private EditText numeField;
    
    /**
     * Câmpul pentru introducerea prenumelui
     */
    private EditText prenumeField;
    
    /**
     * Câmpul pentru introducerea adresei de email
     */
    private EditText emailField;
    
    /**
     * Câmpul pentru introducerea parolei
     */
    private EditText passwordField;
    
    /**
     * Câmpul pentru confirmarea parolei
     */
    private EditText confirmPasswordField;
    
    /**
     * Câmpul pentru introducerea CNP-ului
     */
    private EditText cnpField;
    
    /**
     * Câmpul pentru introducerea datei nașterii
     */
    private EditText birthDateField;
    
    /**
     * Spinner-ul pentru selectarea sexului
     */
    private Spinner sexSpinner;
    
    /**
     * Butonul pentru inițierea procesului de înregistrare
     */
    private Button registerButton;

    /**
     * Opțiunile disponibile pentru sex
     */
    private String[] sexOptions = {"Masculin", "Feminin"};

    /**
     * Metodă apelată la crearea activității
     * 
     * Inițializează interfața utilizator și setează listener-ele:
     * - Configurează toate câmpurile din formular
     * - Setează adapter-ul pentru spinner-ul de sex
     * - Configurează listener pentru extragerea datelor din CNP
     * - Setează listener pentru butonul de înregistrare
     * 
     * @param savedInstanceState Starea salvată a activității (poate fi null)
     */
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_register);

        // Inițializează toate câmpurile din interfață
        numeField = findViewById(R.id.nume);
        prenumeField = findViewById(R.id.prenume);
        emailField = findViewById(R.id.email);
        passwordField = findViewById(R.id.password);
        confirmPasswordField = findViewById(R.id.confirm_password);
        cnpField = findViewById(R.id.cnp);
        birthDateField = findViewById(R.id.data_nasterii);
        sexSpinner = findViewById(R.id.sex_spinner);
        registerButton = findViewById(R.id.register_button);

        // Adapter personalizat pentru spinner-ul de sex cu text alb
        ArrayAdapter<String> adapter = new ArrayAdapter<String>(this, android.R.layout.simple_spinner_item, sexOptions) {
            @Override
            public android.view.View getView(int position, android.view.View convertView, android.view.ViewGroup parent) {
                android.view.View view = super.getView(position, convertView, parent);
                if (view instanceof android.widget.TextView) {
                    ((android.widget.TextView) view).setTextColor(android.graphics.Color.WHITE);
                }
                return view;
            }
        };
        adapter.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item);
        sexSpinner.setAdapter(adapter);

        // Listener pentru extragerea datelor din CNP când câmpul pierde focus
        cnpField.setOnFocusChangeListener((v, hasFocus) -> {
            if (!hasFocus) updateFieldsFromCNP();
        });

        // Listener pentru butonul de înregistrare
        registerButton.setOnClickListener(v -> {
            if (validateForm()) {
                sendRegistrationRequest();
            }
        });
    }

    /**
     * Validează toate câmpurile din formular
     * 
     * Această metodă verifică:
     * - Formatul corect al email-ului
     * - CNP-ul să conțină exact 13 cifre
     * - Data nașterii să fie completată
     * - Parolele să coincidă
     * 
     * @return true dacă toate validările trec, false altfel
     */
    private boolean validateForm() {
        String nume = numeField.getText().toString().trim();
        String prenume = prenumeField.getText().toString().trim();
        String email = emailField.getText().toString().trim();
        String password = passwordField.getText().toString();
        String confirmPassword = confirmPasswordField.getText().toString();
        String cnp = cnpField.getText().toString().trim();
        String birthDate = birthDateField.getText().toString().trim();

        // Validează formatul email-ului
        if (!Pattern.matches("^[^@\\s]+@[^@\\s]+\\.[^@\\s]+$", email)) {
            toast("Email invalid!");
            return false;
        }

        // Validează CNP-ul să conțină exact 13 cifre
        if (!cnp.matches("\\d{13}")) {
            toast("CNP-ul trebuie să conțină exact 13 cifre!");
            return false;
        }

        // Validează data nașterii
        if (birthDate.isEmpty()) {
            toast("Data nașterii este obligatorie!");
            return false;
        }

        // Validează că parolele coincid
        if (!password.equals(confirmPassword)) {
            toast("Parolele nu coincid!");
            return false;
        }

        return true;
    }

    /**
     * Extrage automat informații din CNP și completează câmpurile corespunzătoare
     * 
     * Această metodă analizează CNP-ul și extrage:
     * - Sexul (din prima cifră)
     * - Data nașterii (din cifrele 2-7)
     * - Completează automat spinner-ul de sex și câmpul de dată nașterii
     */
    private void updateFieldsFromCNP() {
        String cnp = cnpField.getText().toString();

        if (cnp.length() >= 7) {
            // Extrage sexul din prima cifră
            int sexDigit = Character.getNumericValue(cnp.charAt(0));
            String sex = (sexDigit % 2 == 0) ? "Feminin" : "Masculin";
            if (sexDigit >= 1 && sexDigit <= 6) {
                sexSpinner.setSelection(sex.equals("Masculin") ? 0 : 1);
            }

            // Extrage data nașterii din cifrele 2-7
            String year = cnp.substring(1, 3);
            String month = cnp.substring(3, 5);
            String day = cnp.substring(5, 7);

            // Determină anul complet în funcție de prima cifră
            String fullYear;
            if (sexDigit == 1 || sexDigit == 2) fullYear = "19" + year;
            else if (sexDigit == 3 || sexDigit == 4) fullYear = "18" + year;
            else if (sexDigit == 5 || sexDigit == 6) fullYear = "20" + year;
            else fullYear = "20" + year; // fallback

            String formattedDate = fullYear + "-" + month + "-" + day;
            birthDateField.setText(formattedDate);
        }
    }

    /**
     * Trimite cererea de înregistrare către server
     * 
     * Această metodă:
     * - Extrage toate datele din formular
     * - Creează cererea POST către server
     * - Procesează răspunsul de la server
     * - Gestionează succesul și erorile
     * - Redirecționează către login după înregistrare reușită
     */
    private void sendRegistrationRequest() {
        String url = "http://10.0.2.2/EasyMed/api/register.php";

        // Extrage toate datele din formular
        String nume = numeField.getText().toString().trim();
        String prenume = prenumeField.getText().toString().trim();
        String email = emailField.getText().toString().trim();
        String password = passwordField.getText().toString();
        String confirmPassword = confirmPasswordField.getText().toString();
        String cnp = cnpField.getText().toString().trim();
        String dataNasterii = birthDateField.getText().toString().trim();
        String sex = sexSpinner.getSelectedItem().toString();

        // Creează cererea POST folosind Volley
        StringRequest stringRequest = new StringRequest(Request.Method.POST, url,
                response -> {
                    Log.d("REGISTER_RESPONSE_RAW", response);
                    try {
                        JSONObject jsonResponse = new JSONObject(response);
                        if (jsonResponse.getString("status").equals("success")) {
                            Toast.makeText(this, "Înregistrare reușită! Revenire la login...", Toast.LENGTH_LONG).show();
                            // Redirecționează către login după 1 secundă
                            new android.os.Handler().postDelayed(() -> {
                                Intent intent = new Intent(RegisterActivity.this, LoginActivity.class);
                                intent.addFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP | Intent.FLAG_ACTIVITY_NEW_TASK);
                                startActivity(intent);
                                finish();
                            }, 1000);
                            finish();
                        } else {
                            String message = jsonResponse.optString("message", "Eroare necunoscută");
                            Toast.makeText(this, "Eroare: " + message, Toast.LENGTH_LONG).show();
                        }
                    } catch (JSONException e) {
                        e.printStackTrace();
                        Toast.makeText(this, "Eroare JSON: " + e.getMessage(), Toast.LENGTH_LONG).show();
                    }
                },
                error -> Toast.makeText(this, "Eroare de rețea: " + error.toString(), Toast.LENGTH_LONG).show()
        ) {
            /**
             * Returnează parametrii pentru cererea POST
             * 
             * @return Map cu toate datele din formular
             */
            @Override
            protected Map<String, String> getParams() {
                Map<String, String> params = new HashMap<>();
                params.put("nume", nume);
                params.put("prenume", prenume);
                params.put("email", email);
                params.put("password", password);
                params.put("confirm_password", confirmPassword);
                params.put("cnp", cnp);
                params.put("data_nasterii", dataNasterii);
                params.put("sex", sex);
                return params;
            }
        };

        // Adaugă cererea la coada Volley
        RequestQueue queue = Volley.newRequestQueue(this);
        queue.add(stringRequest);
    }

    /**
     * Afișează un mesaj Toast
     * 
     * Metodă helper pentru afișarea mesajelor de eroare și informare.
     * 
     * @param msg Mesajul de afișat
     */
    private void toast(String msg) {
        Toast.makeText(this, msg, Toast.LENGTH_LONG).show();
    }
}
package com.example.easymed_mobile;

import android.content.Intent;
import android.os.Bundle;
import android.util.Log;
import android.widget.*;
import androidx.appcompat.app.AppCompatActivity;

import com.android.volley.*;
import com.android.volley.toolbox.*;
import com.android.volley.toolbox.JsonObjectRequest;

import org.json.JSONException;
import org.json.JSONObject;

import java.util.HashMap;
import java.util.Map;
import android.content.SharedPreferences;

/**
 * Activitate pentru autentificarea utilizatorilor în aplicația EasyMed
 * 
 * Această activitate gestionează procesul complet de autentificare pentru pacienți:
 * - Afișează formularul de autentificare cu câmpuri pentru email și parolă
 * - Validează credențialele introduse de utilizator
 * - Comunică cu serverul prin API pentru verificarea autenticității
 * - Salvează datele utilizatorului în SharedPreferences pentru persistență
 * - Redirecționează către dashboard după autentificare reușită
 * - Gestionează erorile de autentificare și de rețea
 * - Oferă navigare către pagina de înregistrare pentru utilizatori noi
 * 
 * <p>Activitatea folosește Volley pentru cererile HTTP și JSONObject pentru
 * parsarea răspunsurilor de la server.</p>
 * 
 * @author EasyMed Mobile Team
 * @version 1.0
 * @since 2024
 */
public class LoginActivity extends AppCompatActivity {

    // ==================== CÂMPURI PRIVATE ====================
    
    /**
     * Câmpul pentru introducerea adresei de email
     */
    private EditText emailField;
    
    /**
     * Câmpul pentru introducerea parolei
     */
    private EditText passwordField;
    
    /**
     * Butonul pentru inițierea procesului de autentificare
     */
    private Button loginButton;
    
    /**
     * URL-ul pentru endpoint-ul de autentificare pe server
     */
    private static final String LOGIN_URL = "http://10.0.2.2/EasyMed/api/login.php";

    /**
     * Metodă apelată la crearea activității
     * 
     * Inițializează interfața utilizator și setează listener-ele pentru butoane:
     * - Configurează câmpurile de email și parolă
     * - Setează listener pentru butonul de login
     * - Configurează navigarea către pagina de înregistrare
     * - Pregătește activitatea pentru interacțiunea cu utilizatorul
     * 
     * @param savedInstanceState Starea salvată a activității (poate fi null)
     */
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_login); // Creează layout-ul

        // Inițializează câmpurile din interfață
        emailField = findViewById(R.id.email);
        passwordField = findViewById(R.id.password);
        loginButton = findViewById(R.id.login_button);

        // Setează listener pentru butonul de login
        loginButton.setOnClickListener(v -> attemptLogin());

        // Configurează navigarea către pagina de înregistrare
        TextView createAccountText = findViewById(R.id.create_account_text);
        createAccountText.setOnClickListener(v -> {
            Intent intent = new Intent(LoginActivity.this, RegisterActivity.class);
            startActivity(intent);
        });
    }

    /**
     * Încearcă autentificarea utilizatorului
     * 
     * Această metodă gestionează întregul proces de autentificare:
     * - Validează câmpurile introduse (email și parolă)
     * - Trimite cererea de autentificare către server prin POST
     * - Procesează răspunsul JSON de la server
     * - Salvează datele utilizatorului în SharedPreferences pentru persistență
     * - Obține ID-ul pacientului asociat utilizatorului
     * - Redirecționează către dashboard sau afișează erori relevante
     * 
     * <p>Metoda folosește Volley pentru cererea HTTP și gestionează
     * atât răspunsurile de succes cât și erorile de rețea.</p>
     */
    private void attemptLogin() {
        // Extrage datele din câmpuri și elimină spațiile albe
        String email = emailField.getText().toString().trim();
        String password = passwordField.getText().toString().trim();

        // Validează câmpurile obligatorii
        if (email.isEmpty() || password.isEmpty()) {
            Toast.makeText(this, "Completați toate câmpurile!", Toast.LENGTH_SHORT).show();
            return;
        }

        // Creează cererea de autentificare folosind Volley
        StringRequest stringRequest = new StringRequest(Request.Method.POST, LOGIN_URL,
                response -> {
                    Log.d("LoginResponse", response);
                    try {
                        JSONObject json = new JSONObject(response);
                        if (json.getString("status").equals("success")) {
                            Toast.makeText(this, "Autentificare reușită!", Toast.LENGTH_SHORT).show();
                            
                            // Salvează user_id în SharedPreferences
                            int userId = json.getInt("user_id");
                            SharedPreferences prefs = getSharedPreferences("easymed_prefs", MODE_PRIVATE);
                            prefs.edit().putInt("user_id", userId).apply();

                            // Obține patient_id folosind noul endpoint
                            String patientIdUrl = "http://10.0.2.2/EasyMed/api/get_patient_id.php?user_id=" + userId;
                            JsonObjectRequest patientIdRequest = new JsonObjectRequest(
                                    Request.Method.GET, patientIdUrl, null,
                                    response2 -> {
                                        if (response2.has("patient_id")) {
                                            int patientId = response2.optInt("patient_id", -1);
                                            prefs.edit().putInt("patient_id", patientId).apply();
                                            
                                            // Pornește dashboard-ul cu ID-ul pacientului
                                            Intent intent = new Intent(this, DashboardPacientActivity.class);
                                            intent.putExtra("patient_id", patientId);
                                            startActivity(intent);
                                            finish(); // Închide activitatea de login
                                        } else {
                                            Toast.makeText(this, "Eroare: pacientul nu a fost găsit pentru acest user_id!", Toast.LENGTH_LONG).show();
                                        }
                                    },
                                    error2 -> {
                                        Toast.makeText(this, "Eroare la obținerea patient_id: " + error2.toString(), Toast.LENGTH_LONG).show();
                                    }
                            );
                            Volley.newRequestQueue(this).add(patientIdRequest);
                        } else {
                            Toast.makeText(this, json.getString("message"), Toast.LENGTH_SHORT).show();
                        }
                    } catch (JSONException e) {
                        e.printStackTrace();
                        Toast.makeText(this, "Eroare la parsarea răspunsului!", Toast.LENGTH_SHORT).show();
                    }
                },
                error -> {
                    Log.e("VolleyError", error.toString());
                    Toast.makeText(this, "Eroare de rețea: " + error.toString(), Toast.LENGTH_SHORT).show();
                }) {

            /**
             * Returnează parametrii pentru cererea POST
             * 
             * Această metodă este suprascrisă pentru a include email-ul și parola
             * în corpul cererii HTTP POST.
             * 
             * @return Map cu email-ul și parola pentru autentificare
             */
            @Override
            protected Map<String, String> getParams() {
                Map<String, String> params = new HashMap<>();
                params.put("email", email);
                params.put("password", password);
                return params;
            }
        };

        // Adaugă cererea la coada Volley pentru execuție
        Volley.newRequestQueue(this).add(stringRequest);
    }
}

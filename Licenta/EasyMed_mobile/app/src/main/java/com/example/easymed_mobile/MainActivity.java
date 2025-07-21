package com.example.easymed_mobile;

import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;

import androidx.appcompat.app.AppCompatActivity;

/**
 * Activitatea principală a aplicației EasyMed
 * 
 * Această activitate servește ca punct de intrare în aplicație și oferă
 * utilizatorilor opțiunea de a se autentifica sau de a se înregistra.
 * 
 * <p>Activitatea afișează un ecran de start cu două butoane principale:
 * - Buton pentru autentificare (navigare către LoginActivity)
 * - Buton pentru înregistrare (navigare către RegisterActivity)</p>
 * 
 * @author EasyMed Mobile Team
 * @version 1.0
 * @since 2024
 */
public class MainActivity extends AppCompatActivity {
    
    /**
     * Metodă apelată la crearea activității
     * 
     * Inițializează interfața utilizator și setează listener-ele pentru butoane:
     * - Configurează layout-ul principal
     * - Inițializează butoanele de login și register
     * - Setează listener-ele pentru navigare către activitățile corespunzătoare
     * 
     * @param savedInstanceState Starea salvată a activității (poate fi null)
     */
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);

        // Inițializează butoanele din interfață
        Button loginBtn = findViewById(R.id.login_button);
        Button registerBtn = findViewById(R.id.register_button);

        // Setează listener pentru butonul de login
        loginBtn.setOnClickListener(v -> {
            Intent intent = new Intent(this, LoginActivity.class);
            startActivity(intent);
        });

        // Setează listener pentru butonul de register
        registerBtn.setOnClickListener(v -> {
            Intent intent = new Intent(this, RegisterActivity.class);
            startActivity(intent);
        });
    }
}

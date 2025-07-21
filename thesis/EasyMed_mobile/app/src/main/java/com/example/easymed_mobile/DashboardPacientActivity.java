package com.example.easymed_mobile;

import android.content.Intent;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.TextView;
import android.widget.Toast;
import androidx.appcompat.app.AppCompatActivity;
import androidx.cardview.widget.CardView;
import android.content.SharedPreferences;

/**
 * Activitate pentru dashboard-ul principal al pacientului în EasyMed
 *
 * Această activitate servește ca pagina principală pentru pacienți după autentificare.
 * Oferă acces rapid la funcționalitățile principale:
 * - Vizualizarea și gestionarea medicilor
 * - Programarea consultațiilor
 * - Accesul la istoricul medical
 * - Gestionarea relațiilor cu alți pacienți
 * - Accesul la profilul personal
 *
 * <p>Activitatea folosește card-uri interactive pentru navigarea
 * către diferitele secțiuni ale aplicației.</p>
 *
 * @author EasyMed Mobile Team
 * @version 1.0
 * @since 2024
 */
public class DashboardPacientActivity extends AppCompatActivity {
    /**
     * Icon-ul pentru accesul la profil
     */
    private TextView profileIcon;
    
    /**
     * Card-uri pentru diferitele funcționalități
     */
    private CardView cardMedici, cardProgramari, cardIstoric, cardRelatii;

    /**
     * Metodă apelată la crearea activității
     *
     * Inițializează interfața, configurează card-urile pentru navigare
     * și setează listener-ele pentru acțiunile utilizatorului.
     *
     * @param savedInstanceState Starea salvată a activității (poate fi null)
     */
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_dashboard_pacient);

        profileIcon = findViewById(R.id.profile_icon);
        profileIcon.setOnClickListener(v -> {
            SharedPreferences prefs = getSharedPreferences("easymed_prefs", MODE_PRIVATE);
            int patientId = prefs.getInt("patient_id", -1);
            if (patientId == -1) {
                Toast.makeText(this, "Eroare: patient_id lipsă!", Toast.LENGTH_LONG).show();
                return;
            }
            Intent intent = new Intent(this, ProfileActivity.class);
            intent.putExtra("patient_id", patientId);
            startActivity(intent);
        });

        // Cards
        cardMedici = findViewById(R.id.card_medici);
        cardProgramari = findViewById(R.id.card_programari);
        cardIstoric = findViewById(R.id.card_istoric);
        cardRelatii = findViewById(R.id.card_relatii);

        /**
         * Navigare către lista de medici
         */
        cardMedici.setOnClickListener(v -> {
            startActivity(new Intent(this, ListaMediciActivity.class));
        });
        
        /**
         * Navigare către programarea consultațiilor
         */
        cardProgramari.setOnClickListener(v -> {
            startActivity(new Intent(this, ProgramareActivity.class));
        });
        
        /**
         * Navigare către istoricul medical
         */
        cardIstoric.setOnClickListener(v -> {
            Intent intent = new Intent(this, IstoricMedicalActivity.class);
            // TODO: Pass the actual patient_id from session/login
            intent.putExtra("patient_id", /* your_patient_id_variable */ 1);
            startActivity(intent);
        });
        
        /**
         * Navigare către gestionarea relațiilor
         */
        cardRelatii.setOnClickListener(v -> {
            Intent intent = new Intent(this, PatientRelationshipsActivity.class);
            // TODO: Pass the actual patient_id from session/login
            intent.putExtra("patient_id", /* your_patient_id_variable */ 1);
            startActivity(intent);
        });
    }
}

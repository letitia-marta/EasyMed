package com.example.easymed_mobile;

import android.os.Bundle;
import android.view.View;
import android.widget.ImageButton;
import android.widget.LinearLayout;
import android.widget.TextView;
import android.widget.Toast;
import androidx.appcompat.app.AppCompatActivity;
import androidx.cardview.widget.CardView;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.List;
import java.util.Locale;
import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

/**
 * Activitate pentru afișarea detaliilor complete ale unei consultații în EasyMed
 *
 * Această activitate permite vizualizarea tuturor informațiilor despre o consultație:
 * - Data și ora consultației
 * - Informațiile despre medic și pacient
 * - Simptomele și diagnosticul
 * - Biletele de trimitere
 * - Biletele de investigații
 * - Rețetele medicale
 *
 * <p>Activitatea primește ID-ul consultației prin intent și încarcă
 * toate detaliile de la server prin API-ul EasyMed.</p>
 *
 * @author EasyMed Mobile Team
 * @version 1.0
 * @since 2024
 */
public class ConsultationDetailsActivity extends AppCompatActivity {
    /**
     * Text-uri pentru informațiile de bază ale consultației
     */
    private TextView consultationDate, consultationTime, doctorName, doctorSpecialty;
    
    /**
     * Text-uri pentru informațiile despre pacient și diagnostic
     */
    private TextView patientName, patientCnp, symptoms, diagnosis;
    
    /**
     * Card-uri pentru diferitele tipuri de documente
     */
    private CardView referralTicketsCard, investigationTicketsCard, prescriptionsCard;
    
    /**
     * Container-e pentru afișarea documentelor
     */
    private LinearLayout referralTicketsContainer, investigationTicketsContainer, prescriptionsContainer;
    
    /**
     * Buton pentru navigarea înapoi
     */
    private ImageButton backButton;

    /**
     * Metodă apelată la crearea activității
     *
     * Inițializează interfața, preia ID-ul consultației din intent
     * și încarcă detaliile consultației de la server.
     *
     * @param savedInstanceState Starea salvată a activității (poate fi null)
     */
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_consultation_details);

        // Initialize views
        initializeViews();

        // Get consultation ID from intent
        int consultationId = getIntent().getIntExtra("consultation_id", 0);
        if (consultationId == 0) {
            Toast.makeText(this, "ID consultație lipsă!", Toast.LENGTH_LONG).show();
            finish();
            return;
        }

        // Load consultation details
        loadConsultationDetails(consultationId);
    }

    /**
     * Inițializează elementele UI și configurează listener-ele
     */
    private void initializeViews() {
        consultationDate = findViewById(R.id.consultation_date);
        consultationTime = findViewById(R.id.consultation_time);
        doctorName = findViewById(R.id.doctor_name);
        doctorSpecialty = findViewById(R.id.doctor_specialty);
        patientName = findViewById(R.id.patient_name);
        patientCnp = findViewById(R.id.patient_cnp);
        symptoms = findViewById(R.id.symptoms);
        diagnosis = findViewById(R.id.diagnosis);
        
        referralTicketsCard = findViewById(R.id.referral_tickets_card);
        investigationTicketsCard = findViewById(R.id.investigation_tickets_card);
        prescriptionsCard = findViewById(R.id.prescriptions_card);
        
        referralTicketsContainer = findViewById(R.id.referral_tickets_container);
        investigationTicketsContainer = findViewById(R.id.investigation_tickets_container);
        prescriptionsContainer = findViewById(R.id.prescriptions_container);
        
        backButton = findViewById(R.id.back_button);
        backButton.setOnClickListener(v -> finish());
    }

    /**
     * Încarcă detaliile consultației de la server
     * 
     * @param consultationId ID-ul consultației pentru care se încarcă detaliile
     */
    private void loadConsultationDetails(int consultationId) {
        ApiService apiService = ApiClient.getClient().create(ApiService.class);
        apiService.getConsultationDetails(consultationId).enqueue(new Callback<ConsultationDetailsResponse>() {
            @Override
            public void onResponse(Call<ConsultationDetailsResponse> call, Response<ConsultationDetailsResponse> response) {
                if (response.isSuccessful() && response.body() != null) {
                    ConsultationDetailsResponse detailsResponse = response.body();
                    if (detailsResponse.isSuccess()) {
                        displayConsultationDetails(detailsResponse);
                    } else {
                        Toast.makeText(ConsultationDetailsActivity.this, 
                            "Eroare: " + detailsResponse.getError(), Toast.LENGTH_LONG).show();
                    }
                } else {
                    Toast.makeText(ConsultationDetailsActivity.this, 
                        "Eroare la încărcarea detaliilor consultației.", Toast.LENGTH_LONG).show();
                }
            }

            @Override
            public void onFailure(Call<ConsultationDetailsResponse> call, Throwable t) {
                Toast.makeText(ConsultationDetailsActivity.this, 
                    "Eroare de conexiune. Vă rugăm să încercați din nou.", Toast.LENGTH_LONG).show();
            }
        });
    }

    /**
     * Afișează detaliile consultației în interfață
     * 
     * @param response Răspunsul de la server cu detaliile consultației
     */
    private void displayConsultationDetails(ConsultationDetailsResponse response) {
        ConsultationDetailsResponse.ConsultationDetails consultation = response.getConsultation();
        
        // Format and display date
        String formattedDate = formatDate(consultation.getDate());
        consultationDate.setText(formattedDate);
        
        // Format and display time
        String formattedTime = formatTime(consultation.getTime());
        consultationTime.setText(formattedTime);
        
        // Display doctor information
        doctorName.setText(consultation.getDoctor().getName());
        doctorSpecialty.setText(consultation.getDoctor().getSpecialty());
        
        // Display patient information
        patientName.setText(consultation.getPatient().getName());
        patientCnp.setText(consultation.getPatient().getCnp());
        
        // Display symptoms
        String symptomsText = consultation.getSymptoms();
        if (symptomsText != null && !symptomsText.trim().isEmpty()) {
            symptoms.setText(symptomsText);
        } else {
            symptoms.setText("Nu există simptome înregistrate");
        }
        
        // Display diagnosis
        String diagnosisText = "";
        if (consultation.getDiagnosisCode() != null && consultation.getDiagnosisName() != null) {
            diagnosisText = consultation.getDiagnosisCode() + " - " + consultation.getDiagnosisName();
        } else if (consultation.getDiagnosisCode() != null) {
            diagnosisText = consultation.getDiagnosisCode();
        } else {
            diagnosisText = "Nu există diagnostic înregistrat";
        }
        diagnosis.setText(diagnosisText);
        
        // Display referral tickets
        List<ConsultationDetailsResponse.ReferralTicket> referralTickets = response.getReferralTickets();
        if (referralTickets != null && !referralTickets.isEmpty()) {
            referralTicketsCard.setVisibility(View.VISIBLE);
            displayReferralTickets(referralTickets);
        }
        
        // Display investigation tickets
        List<ConsultationDetailsResponse.InvestigationTicket> investigationTickets = response.getInvestigationTickets();
        if (investigationTickets != null && !investigationTickets.isEmpty()) {
            investigationTicketsCard.setVisibility(View.VISIBLE);
            displayInvestigationTickets(investigationTickets);
        }
        
        // Display prescriptions
        List<ConsultationDetailsResponse.Prescription> prescriptions = response.getPrescriptions();
        if (prescriptions != null && !prescriptions.isEmpty()) {
            prescriptionsCard.setVisibility(View.VISIBLE);
            displayPrescriptions(prescriptions);
        }
    }

    /**
     * Afișează biletele de trimitere în interfață
     * 
     * @param tickets Lista biletelor de trimitere de afișat
     */
    private void displayReferralTickets(List<ConsultationDetailsResponse.ReferralTicket> tickets) {
        referralTicketsContainer.removeAllViews();
        
        for (ConsultationDetailsResponse.ReferralTicket ticket : tickets) {
            View ticketView = createTicketViewWithCyanCode("Bilet Trimitere #" + ticket.getCode(), 
                "Specializare: " + ticket.getSpecialty());
            referralTicketsContainer.addView(ticketView);
        }
    }

    /**
     * Afișează biletele de investigații în interfață
     * 
     * @param tickets Lista biletelor de investigații de afișat
     */
    private void displayInvestigationTickets(List<ConsultationDetailsResponse.InvestigationTicket> tickets) {
        investigationTicketsContainer.removeAllViews();
        
        for (ConsultationDetailsResponse.InvestigationTicket ticket : tickets) {
            String investigations = ticket.getInvestigations();
            if (investigations != null && !investigations.isEmpty()) {
                String[] investigationList = investigations.split("\\|\\|");
                StringBuilder sb = new StringBuilder();
                for (String investigation : investigationList) {
                    sb.append("• ").append(investigation).append("\n");
                }
                View ticketView = createTicketViewWithCyanCode("Bilet Investigații #" + ticket.getCode(), 
                    sb.toString().trim());
                investigationTicketsContainer.addView(ticketView);
            }
        }
    }

    /**
     * Afișează rețetele medicale în interfață
     * 
     * @param prescriptions Lista rețetelor medicale de afișat
     */
    private void displayPrescriptions(List<ConsultationDetailsResponse.Prescription> prescriptions) {
        prescriptionsContainer.removeAllViews();
        
        for (ConsultationDetailsResponse.Prescription prescription : prescriptions) {
            StringBuilder sb = new StringBuilder();
            sb.append("Medicament: ").append(prescription.getMedication()).append("\n");
            sb.append("Formă farmaceutică: ").append(prescription.getPharmaceuticalForm()).append("\n");
            sb.append("Cantitate: ").append(prescription.getQuantity()).append("\n");
            sb.append("Durata tratamentului: ").append(prescription.getDuration());
            
            View prescriptionView = createTicketViewWithCyanCode("Rețetă Medicală #" + prescription.getCode(), 
                sb.toString());
            prescriptionsContainer.addView(prescriptionView);
        }
    }

    /**
     * Creează un view pentru afișarea unui document cu cod cyan
     * 
     * @param title Titlul documentului
     * @param content Conținutul documentului
     * @return View-ul creat pentru document
     */
    private View createTicketView(String title, String content) {
        CardView cardView = new CardView(this);
        cardView.setCardBackgroundColor(getResources().getColor(android.R.color.transparent));
        cardView.setCardElevation(2);
        cardView.setRadius(8);
        cardView.setUseCompatPadding(true);
        
        LinearLayout.LayoutParams params = new LinearLayout.LayoutParams(
            LinearLayout.LayoutParams.MATCH_PARENT,
            LinearLayout.LayoutParams.WRAP_CONTENT
        );
        params.setMargins(0, 8, 0, 8);
        cardView.setLayoutParams(params);
        
        LinearLayout contentLayout = new LinearLayout(this);
        contentLayout.setOrientation(LinearLayout.VERTICAL);
        contentLayout.setPadding(16, 16, 16, 16);
        contentLayout.setBackgroundColor(getResources().getColor(android.R.color.transparent));
        
        TextView titleView = new TextView(this);
        titleView.setText(title);
        titleView.setTextColor(getResources().getColor(android.R.color.white));
        titleView.setTextSize(16);
        titleView.setTypeface(null, android.graphics.Typeface.BOLD);
        titleView.setLayoutParams(new LinearLayout.LayoutParams(
            LinearLayout.LayoutParams.MATCH_PARENT,
            LinearLayout.LayoutParams.WRAP_CONTENT
        ));
        
        TextView contentView = new TextView(this);
        contentView.setText(content);
        contentView.setTextColor(getResources().getColor(android.R.color.white));
        contentView.setTextSize(14);
        contentView.setLayoutParams(new LinearLayout.LayoutParams(
            LinearLayout.LayoutParams.MATCH_PARENT,
            LinearLayout.LayoutParams.WRAP_CONTENT
        ));
        contentView.setPadding(0, 8, 0, 0);
        
        contentLayout.addView(titleView);
        contentLayout.addView(contentView);
        cardView.addView(contentLayout);
        
        return cardView;
    }

    private View createTicketViewWithCyanCode(String title, String content) {
        CardView cardView = new CardView(this);
        cardView.setCardBackgroundColor(getResources().getColor(android.R.color.transparent));
        cardView.setCardElevation(2);
        cardView.setRadius(8);
        cardView.setUseCompatPadding(true);
        
        LinearLayout.LayoutParams params = new LinearLayout.LayoutParams(
            LinearLayout.LayoutParams.MATCH_PARENT,
            LinearLayout.LayoutParams.WRAP_CONTENT
        );
        params.setMargins(0, 8, 0, 8);
        cardView.setLayoutParams(params);
        
        LinearLayout contentLayout = new LinearLayout(this);
        contentLayout.setOrientation(LinearLayout.VERTICAL);
        contentLayout.setPadding(16, 16, 16, 16);
        contentLayout.setBackgroundColor(getResources().getColor(android.R.color.transparent));
        
        TextView titleView = new TextView(this);
        titleView.setText(title);
        titleView.setTextColor(getResources().getColor(android.R.color.white));
        titleView.setTextSize(16);
        titleView.setTypeface(null, android.graphics.Typeface.BOLD);
        titleView.setLayoutParams(new LinearLayout.LayoutParams(
            LinearLayout.LayoutParams.MATCH_PARENT,
            LinearLayout.LayoutParams.WRAP_CONTENT
        ));
        
        TextView contentView = new TextView(this);
        contentView.setText(content);
        contentView.setTextColor(getResources().getColor(android.R.color.white));
        contentView.setTextSize(14);
        contentView.setLayoutParams(new LinearLayout.LayoutParams(
            LinearLayout.LayoutParams.MATCH_PARENT,
            LinearLayout.LayoutParams.WRAP_CONTENT
        ));
        contentView.setPadding(0, 8, 0, 0);
        
        // Set cyan color for the code in the title
        if (title.contains("#")) {
            titleView.setTextColor(0xFF5CF9C8); // Cyan color
        }
        
        contentLayout.addView(titleView);
        contentLayout.addView(contentView);
        cardView.addView(contentLayout);
        
        return cardView;
    }

    private String formatDate(String input) {
        try {
            SimpleDateFormat inputFormat = new SimpleDateFormat("yyyy-MM-dd", Locale.getDefault());
            SimpleDateFormat outputFormat = new SimpleDateFormat("dd.MM.yyyy", Locale.getDefault());
            return outputFormat.format(inputFormat.parse(input));
        } catch (Exception e) {
            return input != null ? input : "";
        }
    }
    
    private String formatTime(String input) {
        try {
            SimpleDateFormat inputFormat = new SimpleDateFormat("HH:mm:ss", Locale.getDefault());
            SimpleDateFormat outputFormat = new SimpleDateFormat("HH:mm", Locale.getDefault());
            return outputFormat.format(inputFormat.parse(input));
        } catch (Exception e) {
            // If parsing fails, try to extract time from the string
            if (input != null && input.contains(":")) {
                String[] parts = input.split(":");
                if (parts.length >= 2) {
                    return parts[0] + ":" + parts[1];
                }
            }
            return input != null ? input : "";
        }
    }
} 
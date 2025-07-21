package com.example.easymed_mobile;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Button;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;
import java.text.ParseException;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.List;
import java.util.Locale;

/**
 * Adapter pentru afișarea listei de consultații medicale în RecyclerView
 * 
 * Această clasă gestionează afișarea consultațiilor medicale într-o listă interactivă.
 * Fiecare element din listă afișează data, medicul, specializarea, diagnosticul
 * și oferă un buton pentru detalii suplimentare.
 * 
 * <p>Adapter-ul implementează pattern-ul ViewHolder pentru optimizarea
 * performanței și folosește un listener pentru gestionarea acțiunii de detalii.</p>
 * 
 * @author EasyMed Mobile Team
 * @version 1.0
 * @since 2024
 */
public class ConsultationAdapter extends RecyclerView.Adapter<ConsultationAdapter.ConsultationViewHolder> {
    /**
     * Lista de consultații de afișat
     */
    private List<Consultation> consultations = new ArrayList<>();
    
    /**
     * Listener pentru acțiunea de detalii
     */
    private OnDetailsClickListener detailsClickListener;

    /**
     * Interfață pentru gestionarea acțiunii de detalii
     */
    public interface OnDetailsClickListener {
        /**
         * Metodă apelată când utilizatorul dorește detalii despre o consultație
         * 
         * @param consultation Consultația selectată
         */
        void onDetailsClick(Consultation consultation);
    }
    
    /**
     * Setează listener-ul pentru acțiunea de detalii
     * 
     * @param listener Listener-ul pentru detalii
     */
    public void setOnDetailsClickListener(OnDetailsClickListener listener) {
        this.detailsClickListener = listener;
    }

    /**
     * Actualizează lista de consultații și notifică RecyclerView
     * 
     * @param consultations Noua listă de consultații
     */
    public void setConsultations(List<Consultation> consultations) {
        this.consultations = consultations != null ? consultations : new ArrayList<>();
        notifyDataSetChanged();
    }

    /**
     * Creează un nou ViewHolder pentru un element din listă
     * 
     * @param parent ViewGroup-ul părinte
     * @param viewType Tipul de view (nu este folosit în acest adapter)
     * @return Un nou ConsultationViewHolder
     */
    @NonNull
    @Override
    public ConsultationViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_consultation, parent, false);
        return new ConsultationViewHolder(view);
    }

    /**
     * Leagă datele unei consultații de ViewHolder-ul corespunzător
     * 
     * @param holder ViewHolder-ul de populat
     * @param position Poziția consultației în listă
     */
    @Override
    public void onBindViewHolder(@NonNull ConsultationViewHolder holder, int position) {
        Consultation c = consultations.get(position);
        holder.date.setText(formatDate(c.getDate()));
        holder.doctor.setText(c.getDoctor_name() + "\n" + c.getSpecialty());
        holder.diagnosis.setText(c.getDiagnosis());
        
        // Setează listener pentru butonul de detalii
        holder.detailsBtn.setOnClickListener(v -> {
            if (detailsClickListener != null) detailsClickListener.onDetailsClick(c);
        });
    }

    /**
     * Formatează data consultației pentru afișare
     * 
     * @param input Data în format yyyy-MM-dd
     * @return Data formatată pentru afișare (dd.MM.yyyy)
     */
    private String formatDate(String input) {
        try {
            SimpleDateFormat inputFormat = new SimpleDateFormat("yyyy-MM-dd", Locale.getDefault());
            SimpleDateFormat outputFormat = new SimpleDateFormat("dd.MM.yyyy", Locale.getDefault());
            return outputFormat.format(inputFormat.parse(input));
        } catch (ParseException | NullPointerException e) {
            return input != null ? input : "";
        }
    }

    /**
     * Returnează numărul total de consultații din listă
     * 
     * @return Numărul de consultații
     */
    @Override
    public int getItemCount() {
        return consultations.size();
    }

    /**
     * ViewHolder pentru afișarea unei consultații în RecyclerView
     * 
     * Această clasă internă gestionează referințele către elementele
     * UI ale unui element din listă și leagă datele consultației
     * de aceste elemente.
     */
    static class ConsultationViewHolder extends RecyclerView.ViewHolder {
        /**
         * TextView pentru data consultației
         */
        TextView date;
        
        /**
         * TextView pentru medic și specializare
         */
        TextView doctor;
        
        /**
         * TextView pentru diagnostic
         */
        TextView diagnosis;
        
        /**
         * Buton pentru detalii consultație
         */
        Button detailsBtn;
        
        /**
         * Constructor pentru ViewHolder
         * 
         * @param itemView View-ul rădăcină al elementului din listă
         */
        ConsultationViewHolder(@NonNull View itemView) {
            super(itemView);
            date = itemView.findViewById(R.id.consultation_date);
            doctor = itemView.findViewById(R.id.consultation_doctor);
            diagnosis = itemView.findViewById(R.id.consultation_diagnosis);
            detailsBtn = itemView.findViewById(R.id.consultation_details_btn);
        }
    }
} 
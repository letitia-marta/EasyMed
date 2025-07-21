package com.example.easymed_mobile;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;
import java.util.List;

/**
 * Adapter pentru afișarea listei de pacienți în dialoguri de selecție
 *
 * Această clasă gestionează afișarea pacienților într-o listă simplă pentru dialoguri.
 * Fiecare element afișează numele complet și CNP-ul pacientului și permite selecția acestuia.
 *
 * <p>Adapter-ul implementează pattern-ul ViewHolder pentru performanță și folosește
 * un listener pentru gestionarea selecției.</p>
 *
 * @author EasyMed Mobile Team
 * @version 1.0
 * @since 2024
 */
public class PatientDialogAdapter extends RecyclerView.Adapter<PatientDialogAdapter.PatientViewHolder> {
    /**
     * Lista de pacienți de afișat
     */
    private List<Patient> patients;
    /**
     * Listener pentru gestionarea selecției
     */
    private OnPatientClickListener listener;

    /**
     * Interfață pentru gestionarea selecției unui pacient
     */
    public interface OnPatientClickListener {
        /**
         * Metodă apelată când utilizatorul selectează un pacient
         * @param patient Pacientul selectat
         */
        void onPatientClick(Patient patient);
    }

    /**
     * Constructor pentru crearea adapter-ului
     * @param patients Lista de pacienți de afișat
     * @param listener Listener-ul pentru selecție
     */
    public PatientDialogAdapter(List<Patient> patients, OnPatientClickListener listener) {
        this.patients = patients;
        this.listener = listener;
    }

    /**
     * Creează un nou ViewHolder pentru un element din listă
     * @param parent ViewGroup-ul părinte
     * @param viewType Tipul de view (nu este folosit)
     * @return Un nou PatientViewHolder
     */
    @NonNull
    @Override
    public PatientViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext())
                .inflate(android.R.layout.simple_list_item_1, parent, false);
        return new PatientViewHolder(view);
    }

    /**
     * Leagă datele unui pacient de ViewHolder-ul corespunzător
     * @param holder ViewHolder-ul de populat
     * @param position Poziția pacientului în listă
     */
    @Override
    public void onBindViewHolder(@NonNull PatientViewHolder holder, int position) {
        Patient patient = patients.get(position);
        holder.bind(patient);
    }

    /**
     * Returnează numărul total de pacienți din listă
     * @return Numărul de pacienți
     */
    @Override
    public int getItemCount() {
        return patients.size();
    }

    /**
     * Actualizează lista de pacienți și notifică RecyclerView
     * @param newPatients Noua listă de pacienți
     */
    public void updatePatients(List<Patient> newPatients) {
        this.patients = newPatients;
        notifyDataSetChanged();
    }

    /**
     * ViewHolder pentru afișarea unui pacient în dialog
     */
    class PatientViewHolder extends RecyclerView.ViewHolder {
        /**
         * TextView pentru numele și CNP-ul pacientului
         */
        private TextView patientName;

        /**
         * Constructor pentru ViewHolder
         * @param itemView View-ul rădăcină al elementului din listă
         */
        public PatientViewHolder(@NonNull View itemView) {
            super(itemView);
            patientName = itemView.findViewById(android.R.id.text1);
            patientName.setTextColor(itemView.getContext().getResources().getColor(android.R.color.white));
            itemView.setBackgroundColor(itemView.getContext().getResources().getColor(R.color.easymed_secondary));
            itemView.setPadding(16, 12, 16, 12);
        }

        /**
         * Leagă datele pacientului de elementele UI
         * @param patient Pacientul de afișat
         */
        public void bind(Patient patient) {
            patientName.setText(patient.getFullName() + " (CNP: " + patient.getCnp() + ")");
            itemView.setOnClickListener(v -> {
                if (listener != null) {
                    listener.onPatientClick(patient);
                }
            });
        }
    }
} 
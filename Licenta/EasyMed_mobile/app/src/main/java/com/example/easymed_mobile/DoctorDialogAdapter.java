package com.example.easymed_mobile;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;
import java.util.List;

/**
 * Adapter pentru afișarea listei de medici în dialoguri de selecție
 *
 * Această clasă gestionează afișarea medicilor într-o listă simplă pentru dialoguri.
 * Fiecare element afișează numele și specializarea medicului și permite selecția acestuia.
 *
 * <p>Adapter-ul implementează pattern-ul ViewHolder pentru performanță și folosește
 * un listener pentru gestionarea selecției.</p>
 *
 * @author EasyMed Mobile Team
 * @version 1.0
 * @since 2024
 */
public class DoctorDialogAdapter extends RecyclerView.Adapter<DoctorDialogAdapter.DoctorViewHolder> {
    /**
     * Lista de medici de afișat
     */
    private List<Doctor> doctors;
    /**
     * Listener pentru gestionarea selecției
     */
    private OnDoctorClickListener listener;

    /**
     * Interfață pentru gestionarea selecției unui medic
     */
    public interface OnDoctorClickListener {
        /**
         * Metodă apelată când utilizatorul selectează un medic
         * @param doctor Medicul selectat
         */
        void onDoctorClick(Doctor doctor);
    }

    /**
     * Constructor pentru crearea adapter-ului
     * @param doctors Lista de medici de afișat
     * @param listener Listener-ul pentru selecție
     */
    public DoctorDialogAdapter(List<Doctor> doctors, OnDoctorClickListener listener) {
        this.doctors = doctors;
        this.listener = listener;
    }

    /**
     * Creează un nou ViewHolder pentru un element din listă
     * @param parent ViewGroup-ul părinte
     * @param viewType Tipul de view (nu este folosit)
     * @return Un nou DoctorViewHolder
     */
    @NonNull
    @Override
    public DoctorViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext())
                .inflate(android.R.layout.simple_list_item_1, parent, false);
        return new DoctorViewHolder(view);
    }

    /**
     * Leagă datele unui medic de ViewHolder-ul corespunzător
     * @param holder ViewHolder-ul de populat
     * @param position Poziția medicului în listă
     */
    @Override
    public void onBindViewHolder(@NonNull DoctorViewHolder holder, int position) {
        Doctor doctor = doctors.get(position);
        holder.bind(doctor);
    }

    /**
     * Returnează numărul total de medici din listă
     * @return Numărul de medici
     */
    @Override
    public int getItemCount() {
        return doctors.size();
    }

    /**
     * Actualizează lista de medici și notifică RecyclerView
     * @param newDoctors Noua listă de medici
     */
    public void updateDoctors(List<Doctor> newDoctors) {
        this.doctors = newDoctors;
        notifyDataSetChanged();
    }

    /**
     * ViewHolder pentru afișarea unui medic în dialog
     */
    class DoctorViewHolder extends RecyclerView.ViewHolder {
        /**
         * TextView pentru numele și specializarea medicului
         */
        private TextView doctorName;

        /**
         * Constructor pentru ViewHolder
         * @param itemView View-ul rădăcină al elementului din listă
         */
        public DoctorViewHolder(@NonNull View itemView) {
            super(itemView);
            doctorName = itemView.findViewById(android.R.id.text1);
            doctorName.setTextColor(itemView.getContext().getResources().getColor(android.R.color.white));
            itemView.setBackgroundColor(itemView.getContext().getResources().getColor(R.color.easymed_secondary));
            itemView.setPadding(16, 12, 16, 12);
        }

        /**
         * Leagă datele medicului de elementele UI
         * @param doctor Medicul de afișat
         */
        public void bind(Doctor doctor) {
            doctorName.setText("Dr. " + doctor.getFullName() + " - " + doctor.getSpecializare());
            
            itemView.setOnClickListener(v -> {
                if (listener != null) {
                    listener.onDoctorClick(doctor);
                }
            });
        }
    }
} 
package com.example.easymed_mobile;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Button;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;
import java.util.List;

/**
 * Adapter pentru afișarea listei de medici în RecyclerView
 * 
 * Această clasă gestionează afișarea medicilor într-o listă interactivă.
 * Fiecare element din listă afișează numele medicului, specializarea
 * și un buton pentru programarea unei consultații.
 * 
 * <p>Adapter-ul implementează pattern-ul ViewHolder pentru optimizarea
 * performanței și folosește un listener pentru gestionarea acțiunilor
 * utilizatorului.</p>
 * 
 * @author EasyMed Mobile Team
 * @version 1.0
 * @since 2024
 */
public class DoctorAdapter extends RecyclerView.Adapter<DoctorAdapter.DoctorViewHolder> {
    
    /**
     * Lista de medici de afișat
     */
    private List<Doctor> doctors;
    
    /**
     * Listener pentru gestionarea acțiunilor pe medici
     */
    private OnDoctorClickListener listener;

    /**
     * Interfață pentru gestionarea acțiunilor pe medici
     * 
     * Această interfață definește metoda care trebuie implementată
     * de activitatea care folosește adapter-ul pentru a gestiona
     * click-urile pe medici.
     */
    public interface OnDoctorClickListener {
        /**
         * Metodă apelată când utilizatorul face click pe un medic
         * 
         * @param doctor Medicul selectat
         */
        void onDoctorClick(Doctor doctor);
    }

    /**
     * Constructor pentru crearea adapter-ului
     * 
     * @param doctors Lista de medici de afișat
     * @param listener Listener-ul pentru gestionarea acțiunilor
     */
    public DoctorAdapter(List<Doctor> doctors, OnDoctorClickListener listener) {
        this.doctors = doctors;
        this.listener = listener;
    }

    /**
     * Creează un nou ViewHolder pentru un element din listă
     * 
     * @param parent ViewGroup-ul părinte
     * @param viewType Tipul de view (nu este folosit în acest adapter)
     * @return Un nou DoctorViewHolder
     */
    @NonNull
    @Override
    public DoctorViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext())
                .inflate(R.layout.item_doctor, parent, false);
        return new DoctorViewHolder(view);
    }

    /**
     * Leagă datele unui medic de ViewHolder-ul corespunzător
     * 
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
     * 
     * @return Numărul de medici
     */
    @Override
    public int getItemCount() {
        return doctors.size();
    }

    /**
     * Actualizează lista de medici și notifică RecyclerView
     * 
     * @param newDoctors Noua listă de medici
     */
    public void updateDoctors(List<Doctor> newDoctors) {
        this.doctors = newDoctors;
        notifyDataSetChanged();
    }

    /**
     * ViewHolder pentru afișarea unui medic în RecyclerView
     * 
     * Această clasă internă gestionează referințele către elementele
     * UI ale unui element din listă și leagă datele medicului
     * de aceste elemente.
     */
    class DoctorViewHolder extends RecyclerView.ViewHolder {
        /**
         * TextView pentru numele medicului
         */
        private TextView doctorName;
        
        /**
         * TextView pentru specializarea medicului
         */
        private TextView doctorSpecialty;
        
        /**
         * Buton pentru programarea unei consultații
         */
        private Button appointmentButton;

        /**
         * Constructor pentru ViewHolder
         * 
         * @param itemView View-ul rădăcină al elementului din listă
         */
        public DoctorViewHolder(@NonNull View itemView) {
            super(itemView);
            doctorName = itemView.findViewById(R.id.doctor_name);
            doctorSpecialty = itemView.findViewById(R.id.doctor_specialty);
            appointmentButton = itemView.findViewById(R.id.appointment_button);
        }

        /**
         * Leagă datele medicului de elementele UI
         * 
         * @param doctor Medicul de afișat
         */
        public void bind(Doctor doctor) {
            doctorName.setText(doctor.getFullName());
            doctorSpecialty.setText(doctor.getSpecializare());

            appointmentButton.setOnClickListener(v -> {
                if (listener != null) {
                    listener.onDoctorClick(doctor);
                }
            });
        }
    }
} 
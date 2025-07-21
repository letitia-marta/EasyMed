package com.example.easymed_mobile;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Button;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;
import java.util.ArrayList;
import java.util.List;

/**
 * Adapter pentru afișarea listei de programări în RecyclerView
 * 
 * Această clasă gestionează afișarea programărilor într-o listă interactivă.
 * Fiecare element din listă afișează detaliile programării (dată, oră, medic,
 * specializare, motiv) și oferă butoane pentru editare și ștergere.
 * 
 * <p>Adapter-ul implementează pattern-ul ViewHolder pentru optimizarea
 * performanței și folosește listener-e pentru gestionarea acțiunilor
 * de editare și ștergere.</p>
 * 
 * @author EasyMed Mobile Team
 * @version 1.0
 * @since 2024
 */
public class AppointmentAdapter extends RecyclerView.Adapter<AppointmentAdapter.AppointmentViewHolder> {
    /**
     * Lista de programări de afișat
     */
    private List<Appointment> appointments = new ArrayList<>();
    
    /**
     * Listener pentru acțiunea de editare
     */
    private OnEditClickListener editClickListener;
    
    /**
     * Listener pentru acțiunea de ștergere
     */
    private OnDeleteClickListener deleteClickListener;

    /**
     * Interfață pentru gestionarea acțiunii de editare
     */
    public interface OnEditClickListener {
        /**
         * Metodă apelată când utilizatorul dorește să editeze o programare
         * 
         * @param appointment Programarea de editat
         */
        void onEditClick(Appointment appointment);
    }
    
    /**
     * Interfață pentru gestionarea acțiunii de ștergere
     */
    public interface OnDeleteClickListener {
        /**
         * Metodă apelată când utilizatorul dorește să șteargă o programare
         * 
         * @param appointment Programarea de șters
         */
        void onDeleteClick(Appointment appointment);
    }
    
    /**
     * Setează listener-ul pentru acțiunea de editare
     * 
     * @param listener Listener-ul pentru editare
     */
    public void setOnEditClickListener(OnEditClickListener listener) {
        this.editClickListener = listener;
    }
    
    /**
     * Setează listener-ul pentru acțiunea de ștergere
     * 
     * @param listener Listener-ul pentru ștergere
     */
    public void setOnDeleteClickListener(OnDeleteClickListener listener) {
        this.deleteClickListener = listener;
    }

    /**
     * Actualizează lista de programări și notifică RecyclerView
     * 
     * @param appointments Noua listă de programări
     */
    public void setAppointments(List<Appointment> appointments) {
        this.appointments = appointments != null ? appointments : new ArrayList<>();
        notifyDataSetChanged();
    }

    /**
     * Creează un nou ViewHolder pentru un element din listă
     * 
     * @param parent ViewGroup-ul părinte
     * @param viewType Tipul de view (nu este folosit în acest adapter)
     * @return Un nou AppointmentViewHolder
     */
    @NonNull
    @Override
    public AppointmentViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_appointment, parent, false);
        return new AppointmentViewHolder(view);
    }

    /**
     * Leagă datele unei programări de ViewHolder-ul corespunzător
     * 
     * @param holder ViewHolder-ul de populat
     * @param position Poziția programării în listă
     */
    @Override
    public void onBindViewHolder(@NonNull AppointmentViewHolder holder, int position) {
        Appointment a = appointments.get(position);
        holder.date.setText(a.getDate());
        holder.time.setText(a.getTimeSlot());
        holder.doctor.setText(a.getDoctorName());
        holder.specialty.setText(a.getSpecialty());
        holder.reason.setText(a.getConsultationType());
        
        // Setează listener pentru butonul de editare
        holder.editBtn.setOnClickListener(v -> {
            if (editClickListener != null) editClickListener.onEditClick(a);
        });
        
        // Setează listener pentru butonul de ștergere
        holder.deleteBtn.setOnClickListener(v -> {
            if (deleteClickListener != null) deleteClickListener.onDeleteClick(a);
        });
    }

    /**
     * Returnează numărul total de programări din listă
     * 
     * @return Numărul de programări
     */
    @Override
    public int getItemCount() {
        return appointments.size();
    }

    /**
     * ViewHolder pentru afișarea unei programări în RecyclerView
     * 
     * Această clasă internă gestionează referințele către elementele
     * UI ale unui element din listă și leagă datele programării
     * de aceste elemente.
     */
    static class AppointmentViewHolder extends RecyclerView.ViewHolder {
        /**
         * TextView pentru data programării
         */
        TextView date;
        
        /**
         * TextView pentru ora programării
         */
        TextView time;
        
        /**
         * TextView pentru numele medicului
         */
        TextView doctor;
        
        /**
         * TextView pentru specializarea medicului
         */
        TextView specialty;
        
        /**
         * TextView pentru motivul consultației
         */
        TextView reason;
        
        /**
         * Buton pentru editarea programării
         */
        Button editBtn;
        
        /**
         * Buton pentru ștergerea programării
         */
        Button deleteBtn;
        
        /**
         * Constructor pentru ViewHolder
         * 
         * @param itemView View-ul rădăcină al elementului din listă
         */
        AppointmentViewHolder(@NonNull View itemView) {
            super(itemView);
            date = itemView.findViewById(R.id.appointment_date);
            time = itemView.findViewById(R.id.appointment_time);
            doctor = itemView.findViewById(R.id.appointment_doctor);
            specialty = itemView.findViewById(R.id.appointment_specialty);
            reason = itemView.findViewById(R.id.appointment_reason);
            editBtn = itemView.findViewById(R.id.appointment_edit_btn);
            deleteBtn = itemView.findViewById(R.id.appointment_delete_btn);
        }
    }
} 
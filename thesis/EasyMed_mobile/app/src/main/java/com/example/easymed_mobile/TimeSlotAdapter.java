package com.example.easymed_mobile;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Button;
import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;
import java.util.List;
import android.graphics.Color;
import android.widget.TextView;
import com.example.easymed_mobile.AvailableTimeSlot;

/**
 * Adapter pentru afișarea sloturilor de timp disponibile în RecyclerView
 * 
 * Această clasă gestionează afișarea sloturilor de timp pentru programări:
 * - Afișează sloturile disponibile, indisponibile și din trecut
 * - Gestionează selecția slotului de timp
 * - Aplică stiluri diferite în funcție de disponibilitate
 * - Oferă feedback vizual pentru slotul selectat
 * 
 * <p>Adapter-ul implementează pattern-ul ViewHolder pentru optimizarea
 * performanței și folosește un listener pentru gestionarea selecției.</p>
 * 
 * @author EasyMed Mobile Team
 * @version 1.0
 * @since 2024
 */
public class TimeSlotAdapter extends RecyclerView.Adapter<TimeSlotAdapter.ViewHolder> {
    /**
     * Lista de sloturi de timp de afișat
     */
    private List<AvailableTimeSlot> timeSlots;
    
    /**
     * Slotul de timp selectat în prezent
     */
    private String selectedTime;
    
    /**
     * Listener pentru gestionarea selecției sloturilor
     */
    private OnTimeSlotClickListener listener;

    /**
     * Interfață pentru gestionarea selecției sloturilor de timp
     * 
     * Această interfață definește metoda care trebuie implementată
     * de activitatea care folosește adapter-ul pentru a gestiona
     * selecția sloturilor de timp.
     */
    public interface OnTimeSlotClickListener {
        /**
         * Metodă apelată când utilizatorul selectează un slot de timp
         * 
         * @param timeSlot Slotul de timp selectat
         */
        void onTimeSlotClick(String timeSlot);
    }

    /**
     * Constructor pentru crearea adapter-ului
     * 
     * @param timeSlots Lista de sloturi de timp de afișat
     * @param selectedTime Slotul de timp selectat inițial
     * @param listener Listener-ul pentru gestionarea selecției
     */
    public TimeSlotAdapter(List<AvailableTimeSlot> timeSlots, String selectedTime, OnTimeSlotClickListener listener) {
        this.timeSlots = timeSlots;
        this.selectedTime = selectedTime;
        this.listener = listener;
    }

    /**
     * Creează un nou ViewHolder pentru un element din listă
     * 
     * @param parent ViewGroup-ul părinte
     * @param viewType Tipul de view (nu este folosit în acest adapter)
     * @return Un nou ViewHolder
     */
    @NonNull
    @Override
    public ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext()).inflate(R.layout.item_time_slot, parent, false);
        return new ViewHolder(view);
    }

    /**
     * Leagă datele unui slot de timp de ViewHolder-ul corespunzător
     * 
     * Această metodă aplică stiluri diferite în funcție de disponibilitatea
     * slotului și gestionează selecția utilizatorului.
     * 
     * @param holder ViewHolder-ul de populat
     * @param position Poziția slotului în listă
     */
    @Override
    public void onBindViewHolder(@NonNull ViewHolder holder, int position) {
        AvailableTimeSlot slot = timeSlots.get(position);
        holder.timeButton.setText(slot.getTime());
        boolean isSelected = slot.getTime().equals(selectedTime);
        
        // Aplică stiluri diferite în funcție de disponibilitate
        if (!slot.isAvailable()) {
            holder.itemView.setBackgroundResource(R.drawable.bg_time_slot_unavailable);
            holder.timeButton.setTextColor(Color.WHITE);
            holder.itemView.setEnabled(false);
        } else if (slot.isPast()) {
            holder.itemView.setBackgroundResource(android.R.color.darker_gray);
            holder.timeButton.setTextColor(Color.LTGRAY);
            holder.itemView.setEnabled(false);
        } else if (isSelected) {
            holder.itemView.setBackgroundResource(R.drawable.bg_time_slot_selected);
            holder.timeButton.setTextColor(Color.WHITE);
            holder.itemView.setEnabled(true);
        } else {
            holder.itemView.setBackgroundResource(R.drawable.bg_time_slot_available);
            holder.timeButton.setTextColor(Color.BLACK);
            holder.itemView.setEnabled(true);
        }
        
        // Setează listener pentru selecția slotului
        holder.itemView.setOnClickListener(v -> {
            if (slot.isAvailable() && !slot.isPast()) {
                selectedTime = slot.getTime();
                notifyDataSetChanged();
                listener.onTimeSlotClick(slot.getTime());
            }
        });
    }

    /**
     * Returnează numărul total de sloturi de timp din listă
     * 
     * @return Numărul de sloturi de timp
     */
    @Override
    public int getItemCount() {
        return timeSlots.size();
    }

    /**
     * ViewHolder pentru afișarea unui slot de timp în RecyclerView
     * 
     * Această clasă internă gestionează referințele către elementele
     * UI ale unui element din listă.
     */
    public static class ViewHolder extends RecyclerView.ViewHolder {
        /**
         * Butonul pentru afișarea slotului de timp
         */
        Button timeButton;
        
        /**
         * Constructor pentru ViewHolder
         * 
         * @param itemView View-ul rădăcină al elementului din listă
         */
        public ViewHolder(@NonNull View itemView) {
            super(itemView);
            timeButton = itemView.findViewById(R.id.time_slot_button);
        }
    }
} 
package com.example.easymed_mobile;

import com.google.gson.annotations.SerializedName;

/**
 * Clasă pentru reprezentarea unui slot de timp disponibil pentru programări
 * în aplicația EasyMed
 * 
 * Această clasă conține informațiile despre un slot de timp:
 * - Ora slotului
 * - Disponibilitatea slotului (true/false)
 * - Dacă slotul este din trecut (true/false)
 * 
 * <p>Clasa este folosită pentru afișarea sloturilor disponibile
 * la programarea unei consultații.</p>
 * 
 * <p>Clasa folosește anotările Gson pentru maparea câmpurilor JSON
 * la proprietățile Java.</p>
 * 
 * @author EasyMed Mobile Team
 * @version 1.0
 * @since 2024
 */
public class AvailableTimeSlot {
    /**
     * Ora slotului în format string (ex: "09:00", "14:30")
     */
    @SerializedName("time")
    private String time;
    
    /**
     * Indică dacă slotul este disponibil pentru programare
     */
    @SerializedName("available")
    private boolean available;
    
    /**
     * Indică dacă slotul este din trecut
     */
    @SerializedName("isPast")
    private boolean isPast;

    /**
     * Returnează ora slotului
     * 
     * @return Ora slotului în format string
     */
    public String getTime() {
        return time;
    }

    /**
     * Setează ora slotului
     * 
     * @param time Ora slotului ca String
     */
    public void setTime(String time) {
        this.time = time;
    }

    /**
     * Verifică dacă slotul este disponibil pentru programare
     * 
     * @return true dacă slotul este disponibil, false altfel
     */
    public boolean isAvailable() {
        return available;
    }

    /**
     * Setează disponibilitatea slotului
     * 
     * @param available true dacă slotul este disponibil, false altfel
     */
    public void setAvailable(boolean available) {
        this.available = available;
    }

    /**
     * Verifică dacă slotul este din trecut
     * 
     * @return true dacă slotul este din trecut, false altfel
     */
    public boolean isPast() {
        return isPast;
    }

    /**
     * Setează dacă slotul este din trecut
     * 
     * @param isPast true dacă slotul este din trecut, false altfel
     */
    public void setIsPast(boolean isPast) {
        this.isPast = isPast;
    }
} 
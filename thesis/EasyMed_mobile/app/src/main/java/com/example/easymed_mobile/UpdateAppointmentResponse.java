package com.example.easymed_mobile;

import com.google.gson.annotations.SerializedName;

/**
 * Clasă pentru reprezentarea răspunsului la actualizarea unei programări
 * 
 * Această clasă conține rezultatele operației de actualizare a unei programări:
 * - Statusul operației (succes/eșec)
 * - Mesajul de răspuns de la server
 * - Mesajul de eroare (dacă operația a eșuat)
 * 
 * <p>Clasa este folosită pentru deserializarea răspunsurilor JSON de la
 * serverul de actualizare programări.</p>
 * 
 * <p>Clasa folosește anotările Gson pentru maparea câmpurilor JSON
 * la proprietățile Java.</p>
 * 
 * @author EasyMed Mobile Team
 * @version 1.0
 * @since 2024
 */
public class UpdateAppointmentResponse {
    /**
     * Indică dacă actualizarea programării a fost reușită
     */
    @SerializedName("success")
    private boolean success;
    
    /**
     * Mesajul de răspuns de la server
     */
    @SerializedName("message")
    private String message;
    
    /**
     * Mesajul de eroare în caz de eșec
     */
    @SerializedName("error")
    private String error;

    // ==================== METODE GETTER ====================
    
    /**
     * Verifică dacă actualizarea programării a fost reușită
     * 
     * @return true dacă actualizarea a fost reușită, false altfel
     */
    public boolean isSuccess() {
        return success;
    }

    /**
     * Returnează mesajul de răspuns de la server
     * 
     * @return Mesajul de răspuns ca String
     */
    public String getMessage() {
        return message;
    }

    /**
     * Returnează mesajul de eroare în caz de eșec
     * 
     * @return Mesajul de eroare ca String
     */
    public String getError() {
        return error;
    }

    // ==================== METODE SETTER ====================
    
    /**
     * Setează statusul operației de actualizare
     * 
     * @param success true dacă actualizarea a fost reușită, false altfel
     */
    public void setSuccess(boolean success) {
        this.success = success;
    }

    /**
     * Setează mesajul de răspuns de la server
     * 
     * @param message Mesajul de răspuns ca String
     */
    public void setMessage(String message) {
        this.message = message;
    }

    /**
     * Setează mesajul de eroare
     * 
     * @param error Mesajul de eroare ca String
     */
    public void setError(String error) {
        this.error = error;
    }
} 
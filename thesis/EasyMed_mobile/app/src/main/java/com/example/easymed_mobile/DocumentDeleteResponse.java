package com.example.easymed_mobile;

import com.google.gson.annotations.SerializedName;

/**
 * Clasă pentru reprezentarea răspunsului la ștergerea unui document medical
 * 
 * Această clasă conține rezultatele operației de ștergere a unui document:
 * - Statusul operației (succes/eșec)
 * - Mesajul de răspuns de la server
 * 
 * <p>Clasa este folosită pentru deserializarea răspunsurilor JSON de la
 * serverul de ștergere documente.</p>
 * 
 * <p>Clasa folosește anotările Gson pentru maparea câmpurilor JSON
 * la proprietățile Java.</p>
 * 
 * @author EasyMed Mobile Team
 * @version 1.0
 * @since 2024
 */
public class DocumentDeleteResponse {
    /**
     * Indică dacă ștergerea documentului a fost reușită
     */
    @SerializedName("success")
    private boolean success;

    /**
     * Mesajul de răspuns de la server
     */
    @SerializedName("message")
    private String message;

    /**
     * Constructor pentru crearea unui obiect DocumentDeleteResponse
     * 
     * Creează o nouă instanță cu rezultatele operației de ștergere.
     * 
     * @param success Indică dacă ștergerea a fost reușită
     * @param message Mesajul de răspuns de la server
     */
    public DocumentDeleteResponse(boolean success, String message) {
        this.success = success;
        this.message = message;
    }

    // ==================== METODE GETTER ====================
    
    /**
     * Verifică dacă ștergerea documentului a fost reușită
     * 
     * @return true dacă ștergerea a fost reușită, false altfel
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
} 
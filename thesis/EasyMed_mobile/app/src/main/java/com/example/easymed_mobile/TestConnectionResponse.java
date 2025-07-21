package com.example.easymed_mobile;

import com.google.gson.annotations.SerializedName;

/**
 * Clasă pentru reprezentarea răspunsului la testul de conexiune cu serverul
 * 
 * Această clasă conține rezultatele testului de conectivitate cu serverul:
 * - Statusul conexiunii (succes/eșec)
 * - Mesajul de răspuns de la server
 * - Timestamp-ul răspunsului
 * 
 * <p>Clasa este folosită pentru verificarea conectivității cu serverul
 * și pentru debugging-ul conexiunilor de rețea.</p>
 * 
 * <p>Clasa folosește anotările Gson pentru maparea câmpurilor JSON
 * la proprietățile Java.</p>
 * 
 * @author EasyMed Mobile Team
 * @version 1.0
 * @since 2024
 */
public class TestConnectionResponse {
    /**
     * Indică dacă testul de conexiune a fost reușit
     */
    @SerializedName("success")
    private boolean success;
    
    /**
     * Mesajul de răspuns de la server
     */
    @SerializedName("message")
    private String message;
    
    /**
     * Timestamp-ul răspunsului de la server
     */
    @SerializedName("timestamp")
    private String timestamp;
    
    // ==================== METODE GETTER ====================
    
    /**
     * Verifică dacă testul de conexiune a fost reușit
     * 
     * @return true dacă conexiunea a fost reușită, false altfel
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
     * Returnează timestamp-ul răspunsului de la server
     * 
     * @return Timestamp-ul ca String
     */
    public String getTimestamp() {
        return timestamp;
    }
} 
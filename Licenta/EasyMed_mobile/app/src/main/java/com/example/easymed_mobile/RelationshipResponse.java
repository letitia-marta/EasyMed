package com.example.easymed_mobile;

import com.google.gson.annotations.SerializedName;

/**
 * Clasă pentru reprezentarea răspunsului la operații cu relații în EasyMed
 * 
 * Această clasă conține rezultatele operațiilor cu relații între pacienți:
 * - Statusul operației (succes/eșec)
 * - Mesajul de răspuns de la server
 * - Mesajul de eroare (dacă operația a eșuat)
 * 
 * <p>Clasa este folosită pentru deserializarea răspunsurilor JSON de la
 * serverul de gestionare a relațiilor între pacienți.</p>
 * 
 * <p>Clasa folosește anotările Gson pentru maparea câmpurilor JSON
 * la proprietățile Java.</p>
 * 
 * @author EasyMed Mobile Team
 * @version 1.0
 * @since 2024
 */
public class RelationshipResponse {
    /**
     * Indică dacă operația cu relația a fost reușită
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

    /**
     * Verifică dacă operația cu relația a fost reușită
     * 
     * @return true dacă operația a fost reușită, false altfel
     */
    public boolean isSuccess() { return success; }
    
    /**
     * Returnează mesajul de răspuns de la server
     * 
     * @return Mesajul de răspuns ca String
     */
    public String getMessage() { return message; }
    
    /**
     * Returnează mesajul de eroare în caz de eșec
     * 
     * @return Mesajul de eroare ca String
     */
    public String getError() { return error; }
} 
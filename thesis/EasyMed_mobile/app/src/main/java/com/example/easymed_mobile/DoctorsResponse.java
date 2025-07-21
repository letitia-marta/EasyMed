package com.example.easymed_mobile;

import com.google.gson.annotations.SerializedName;
import java.util.List;

/**
 * Clasă pentru reprezentarea răspunsului la cererea listei de medici în EasyMed
 * 
 * Această clasă conține rezultatele cererii pentru lista de medici:
 * - Statusul operației (succes/eșec)
 * - Lista de medici returnată de server
 * - Mesajul de eroare (dacă operația a eșuat)
 * 
 * <p>Clasa este folosită pentru deserializarea răspunsurilor JSON de la
 * serverul de gestionare a medicilor.</p>
 * 
 * <p>Clasa folosește anotările Gson pentru maparea câmpurilor JSON
 * la proprietățile Java.</p>
 * 
 * @author EasyMed Mobile Team
 * @version 1.0
 * @since 2024
 */
public class DoctorsResponse {
    /**
     * Indică dacă cererea pentru lista de medici a fost reușită
     */
    @SerializedName("success")
    private boolean success;
    
    /**
     * Lista de medici returnată de server
     */
    @SerializedName("doctors")
    private List<Doctor> doctors;
    
    /**
     * Mesajul de eroare în caz de eșec
     */
    @SerializedName("error")
    private String error;

    /**
     * Verifică dacă cererea pentru lista de medici a fost reușită
     * 
     * @return true dacă cererea a fost reușită, false altfel
     */
    public boolean isSuccess() {
        return success;
    }

    /**
     * Returnează lista de medici
     * 
     * @return Lista de medici ca List<Doctor>
     */
    public List<Doctor> getDoctors() {
        return doctors;
    }

    /**
     * Returnează mesajul de eroare în caz de eșec
     * 
     * @return Mesajul de eroare ca String
     */
    public String getError() {
        return error;
    }
} 
package com.example.easymed_mobile;

import com.google.gson.annotations.SerializedName;

/**
 * Clasă pentru reprezentarea unui tip de relație în aplicația EasyMed
 * 
 * Această clasă conține informațiile despre un tip de relație disponibil
 * în sistem: ID-ul unic și denumirea relației (ex: părinte, copil, soț, etc.).
 * 
 * <p>Clasa este folosită pentru afișarea listei de tipuri de relații
 * disponibile în interfața utilizator și pentru gestionarea relațiilor
 * între pacienți.</p>
 * 
 * <p>Clasa folosește anotările Gson pentru maparea câmpurilor JSON
 * la proprietățile Java.</p>
 * 
 * @author EasyMed Mobile Team
 * @version 1.0
 * @since 2024
 */
public class RelationshipType {
    /**
     * ID-ul unic al tipului de relație în baza de date
     */
    @SerializedName("id")
    private int id;
    
    /**
     * Denumirea tipului de relație (ex: părinte, copil, soț, etc.)
     */
    @SerializedName("denumire")
    private String denumire;

    // ==================== METODE GETTER ====================
    
    /**
     * Returnează ID-ul unic al tipului de relație
     * 
     * @return ID-ul tipului de relație ca întreg
     */
    public int getId() { 
        return id; 
    }
    
    /**
     * Returnează denumirea tipului de relație
     * 
     * @return Denumirea tipului de relație ca String
     */
    public String getDenumire() { 
        return denumire; 
    }
} 
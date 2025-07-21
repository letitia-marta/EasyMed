package com.example.easymed_mobile;

import com.google.gson.annotations.SerializedName;

/**
 * Clasă pentru reprezentarea unei relații între pacienți în aplicația EasyMed
 * 
 * Această clasă conține informațiile despre o relație între doi pacienți:
 * - ID-ul relației și ID-urile pacienților implicați
 * - Tipul de relație (părinte, copil, soț, etc.)
 * - Datele personale ale pacientului cu care există relația
 * 
 * <p>Clasa este folosită pentru gestionarea relațiilor familiale
 * și medicale între pacienți în sistemul EasyMed.</p>
 * 
 * <p>Clasa folosește anotările Gson pentru maparea câmpurilor JSON
 * la proprietățile Java.</p>
 * 
 * @author EasyMed Mobile Team
 * @version 1.0
 * @since 2024
 */
public class Relationship {
    /**
     * ID-ul unic al relației în baza de date
     */
    @SerializedName("id")
    private int id;
    
    /**
     * ID-ul pacientului principal (cel care are relația)
     */
    @SerializedName("pacient_id")
    private int pacientId;
    
    /**
     * ID-ul pacientului cu care există relația
     */
    @SerializedName("pacient_relat_id")
    private int pacientRelatId;
    
    /**
     * Tipul de relație (părinte, copil, soț, etc.)
     */
    @SerializedName("tip_relatie")
    private String tipRelatie;
    
    /**
     * Numele de familie al pacientului cu care există relația
     */
    @SerializedName("nume")
    private String nume;
    
    /**
     * Prenumele pacientului cu care există relația
     */
    @SerializedName("prenume")
    private String prenume;
    
    /**
     * Codul Numeric Personal al pacientului cu care există relația
     */
    @SerializedName("cnp")
    private String cnp;
    
    /**
     * Sexul pacientului cu care există relația (M/F)
     */
    @SerializedName("sex")
    private String sex;

    // ==================== METODE GETTER ====================
    
    /**
     * Returnează ID-ul unic al relației
     * 
     * @return ID-ul relației ca întreg
     */
    public int getId() { 
        return id; 
    }
    
    /**
     * Returnează ID-ul pacientului principal
     * 
     * @return ID-ul pacientului principal ca întreg
     */
    public int getPacientId() { 
        return pacientId; 
    }
    
    /**
     * Returnează ID-ul pacientului cu care există relația
     * 
     * @return ID-ul pacientului cu care există relația ca întreg
     */
    public int getPacientRelatId() { 
        return pacientRelatId; 
    }
    
    /**
     * Returnează tipul de relație
     * 
     * @return Tipul de relație ca String
     */
    public String getTipRelatie() { 
        return tipRelatie; 
    }
    
    /**
     * Returnează numele de familie al pacientului cu care există relația
     * 
     * @return Numele ca String
     */
    public String getNume() { 
        return nume; 
    }
    
    /**
     * Returnează prenumele pacientului cu care există relația
     * 
     * @return Prenumele ca String
     */
    public String getPrenume() { 
        return prenume; 
    }
    
    /**
     * Returnează CNP-ul pacientului cu care există relația
     * 
     * @return CNP-ul ca String
     */
    public String getCnp() { 
        return cnp; 
    }
    
    /**
     * Returnează sexul pacientului cu care există relația
     * 
     * @return Sexul ca String (M pentru masculin, F pentru feminin)
     */
    public String getSex() { 
        return sex; 
    }
    
    /**
     * Returnează numele complet al pacientului cu care există relația
     * 
     * Această metodă combină numele și prenumele într-un singur String
     * pentru afișare în interfața utilizator.
     * 
     * @return Numele complet ca String
     */
    public String getFullName() { 
        return nume + " " + prenume; 
    }
} 
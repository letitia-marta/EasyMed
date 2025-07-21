package com.example.easymed_mobile;

/**
 * Clasă pentru reprezentarea unei consultații medicale în aplicația EasyMed
 * 
 * Această clasă conține informațiile despre o consultație medicală:
 * - ID-ul unic al consultației
 * - Data și ora consultației
 * - Informații despre medic (nume, specializare)
 * - Diagnosticul stabilit
 * 
 * <p>Clasa este folosită pentru afișarea istoricului consultațiilor
 * unui pacient în interfața utilizator.</p>
 * 
 * @author EasyMed Mobile Team
 * @version 1.0
 * @since 2024
 */
public class Consultation {
    /**
     * ID-ul unic al consultației în baza de date
     */
    private int id;
    
    /**
     * Data consultației în format string
     */
    private String date;
    
    /**
     * Ora consultației în format string
     */
    private String time;
    
    /**
     * Numele complet al medicului care a efectuat consultația
     */
    private String doctor_name;
    
    /**
     * Specializarea medicului
     */
    private String specialty;
    
    /**
     * Diagnosticul stabilit în urma consultației
     */
    private String diagnosis;

    // ==================== METODE GETTER ====================
    
    /**
     * Returnează ID-ul unic al consultației
     * 
     * @return ID-ul consultației ca întreg
     */
    public int getId() { 
        return id; 
    }
    
    /**
     * Returnează data consultației
     * 
     * @return Data consultației în format string
     */
    public String getDate() { 
        return date; 
    }
    
    /**
     * Returnează ora consultației
     * 
     * @return Ora consultației în format string
     */
    public String getTime() { 
        return time; 
    }
    
    /**
     * Returnează numele complet al medicului
     * 
     * @return Numele medicului ca String
     */
    public String getDoctor_name() { 
        return doctor_name; 
    }
    
    /**
     * Returnează specializarea medicului
     * 
     * @return Specializarea medicului ca String
     */
    public String getSpecialty() { 
        return specialty; 
    }
    
    /**
     * Returnează diagnosticul stabilit
     * 
     * @return Diagnosticul ca String
     */
    public String getDiagnosis() { 
        return diagnosis; 
    }
} 
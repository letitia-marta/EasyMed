package com.example.easymed_mobile;

import com.google.gson.annotations.SerializedName;

/**
 * Clasă pentru reprezentarea unui medic în aplicația EasyMed
 * 
 * Această clasă conține informațiile despre un medic și oferă metode
 * pentru accesarea și modificarea acestor date. Clasa este folosită
 * pentru serializarea/deserializarea datelor din API și pentru
 * afișarea informațiilor despre medici în interfața utilizator.
 * 
 * <p>Clasa folosește anotările Gson pentru maparea câmpurilor JSON
 * la proprietățile Java.</p>
 * 
 * @author EasyMed Mobile Team
 * @version 1.0
 * @since 2024
 */
public class Doctor {
    /**
     * ID-ul unic al medicului în baza de date
     */
    @SerializedName("id")
    private int id;
    
    /**
     * Numele de familie al medicului
     */
    @SerializedName("nume")
    private String nume;
    
    /**
     * Prenumele medicului
     */
    @SerializedName("prenume")
    private String prenume;
    
    /**
     * Specializarea medicală a medicului
     */
    @SerializedName("specializare")
    private String specializare;

    /**
     * Constructor pentru crearea unui obiect Doctor
     * 
     * Creează o nouă instanță de Doctor cu toate datele necesare.
     * 
     * @param id ID-ul unic al medicului
     * @param nume Numele medicului
     * @param prenume Prenumele medicului
     * @param specializare Specializarea medicală
     */
    public Doctor(int id, String nume, String prenume, String specializare) {
        this.id = id;
        this.nume = nume;
        this.prenume = prenume;
        this.specializare = specializare;
    }

    // ==================== METODE GETTER ====================
    
    /**
     * Returnează ID-ul unic al medicului
     * 
     * @return ID-ul medicului ca întreg
     */
    public int getId() { 
        return id; 
    }
    
    /**
     * Returnează numele de familie al medicului
     * 
     * @return Numele medicului ca String
     */
    public String getNume() { 
        return nume; 
    }
    
    /**
     * Returnează prenumele medicului
     * 
     * @return Prenumele medicului ca String
     */
    public String getPrenume() { 
        return prenume; 
    }
    
    /**
     * Returnează specializarea medicală a medicului
     * 
     * @return Specializarea medicului ca String
     */
    public String getSpecializare() { 
        return specializare; 
    }
    
    /**
     * Returnează numele complet al medicului (nume + prenume)
     * 
     * Această metodă combină numele și prenumele într-un singur String
     * pentru afișare în interfața utilizator.
     * 
     * @return Numele complet al medicului
     */
    public String getFullName() {
        return nume + " " + prenume;
    }

    // ==================== METODE SETTER ====================
    
    /**
     * Setează ID-ul unic al medicului
     * 
     * @param id ID-ul medicului ca întreg
     */
    public void setId(int id) { 
        this.id = id; 
    }
    
    /**
     * Setează numele de familie al medicului
     * 
     * @param nume Numele medicului ca String
     */
    public void setNume(String nume) { 
        this.nume = nume; 
    }
    
    /**
     * Setează prenumele medicului
     * 
     * @param prenume Prenumele medicului ca String
     */
    public void setPrenume(String prenume) { 
        this.prenume = prenume; 
    }
    
    /**
     * Setează specializarea medicală a medicului
     * 
     * @param specializare Specializarea medicului ca String
     */
    public void setSpecializare(String specializare) { 
        this.specializare = specializare; 
    }
} 
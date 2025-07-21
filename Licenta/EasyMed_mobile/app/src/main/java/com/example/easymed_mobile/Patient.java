package com.example.easymed_mobile;

import com.google.gson.annotations.SerializedName;

/**
 * Clasă pentru reprezentarea unui pacient în aplicația EasyMed
 * 
 * Această clasă conține informațiile despre un pacient și oferă metode
 * pentru accesarea acestor date. Clasa este folosită pentru serializarea/
 * deserializarea datelor din API și pentru afișarea informațiilor despre
 * pacienți în interfața utilizator.
 * 
 * <p>Clasa folosește anotările Gson pentru maparea câmpurilor JSON
 * la proprietățile Java.</p>
 * 
 * @author EasyMed Mobile Team
 * @version 1.0
 * @since 2024
 */
public class Patient {
    /**
     * ID-ul unic al pacientului în baza de date
     */
    @SerializedName("id")
    private int id;
    
    /**
     * Numele de familie al pacientului
     */
    @SerializedName("nume")
    private String nume;
    
    /**
     * Prenumele pacientului
     */
    @SerializedName("prenume")
    private String prenume;
    
    /**
     * Codul Numeric Personal al pacientului
     */
    @SerializedName("cnp")
    private String cnp;
    
    /**
     * Sexul pacientului (M/F)
     */
    @SerializedName("sex")
    private String sex;

    /**
     * Data nașterii pacientului în format string
     */
    @SerializedName("data_nasterii")
    private String dataNasterii;

    /**
     * Adresa completă a pacientului
     */
    @SerializedName("adresa")
    private String adresa;

    /**
     * Grupa sanguină a pacientului
     */
    @SerializedName("grupa_sanguina")
    private String grupaSanguina;

    /**
     * Adresa de email a pacientului
     */
    @SerializedName("email")
    private String email;

    // ==================== METODE GETTER ====================
    
    /**
     * Returnează ID-ul unic al pacientului
     * 
     * @return ID-ul pacientului ca întreg
     */
    public int getId() { 
        return id; 
    }
    
    /**
     * Returnează numele de familie al pacientului
     * 
     * @return Numele pacientului ca String
     */
    public String getNume() { 
        return nume; 
    }
    
    /**
     * Returnează prenumele pacientului
     * 
     * @return Prenumele pacientului ca String
     */
    public String getPrenume() { 
        return prenume; 
    }
    
    /**
     * Returnează Codul Numeric Personal al pacientului
     * 
     * @return CNP-ul pacientului ca String
     */
    public String getCnp() { 
        return cnp; 
    }
    
    /**
     * Returnează sexul pacientului
     * 
     * @return Sexul pacientului (M pentru masculin, F pentru feminin)
     */
    public String getSex() { 
        return sex; 
    }
    
    /**
     * Returnează numele complet al pacientului (nume + prenume)
     * 
     * Această metodă combină numele și prenumele într-un singur String
     * pentru afișare în interfața utilizator.
     * 
     * @return Numele complet al pacientului
     */
    public String getFullName() { 
        return nume + " " + prenume; 
    }
    
    /**
     * Returnează data nașterii pacientului
     * 
     * @return Data nașterii în format string
     */
    public String getDataNasterii() { 
        return dataNasterii; 
    }
    
    /**
     * Returnează adresa completă a pacientului
     * 
     * @return Adresa pacientului ca String
     */
    public String getAdresa() { 
        return adresa; 
    }
    
    /**
     * Returnează grupa sanguină a pacientului
     * 
     * @return Grupa sanguină ca String
     */
    public String getGrupaSanguina() { 
        return grupaSanguina; 
    }
    
    /**
     * Returnează adresa de email a pacientului
     * 
     * @return Email-ul pacientului ca String
     */
    public String getEmail() { 
        return email; 
    }
} 
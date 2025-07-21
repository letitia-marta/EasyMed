package com.example.easymed_mobile;

/**
 * Clasă pentru reprezentarea unui utilizator în aplicația EasyMed
 * 
 * Această clasă conține informațiile de bază despre un utilizator:
 * ID-ul unic și adresa de email. Clasa este folosită pentru
 * serializarea/deserializarea datelor din API și pentru gestionarea
 * utilizatorilor în aplicație.
 * 
 * <p>Clasa este simplă și conține doar informațiile esențiale
 * necesare pentru identificarea și autentificarea utilizatorului.</p>
 * 
 * @author EasyMed Mobile Team
 * @version 1.0
 * @since 2024
 */
public class User {
    /**
     * ID-ul unic al utilizatorului în baza de date
     */
    public int id;
    
    /**
     * Adresa de email a utilizatorului (folosită pentru autentificare)
     */
    public String email;
}

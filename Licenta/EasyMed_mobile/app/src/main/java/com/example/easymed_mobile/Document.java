package com.example.easymed_mobile;

import com.google.gson.annotations.SerializedName;

/**
 * Clasă pentru reprezentarea unui document medical în aplicația EasyMed
 * 
 * Această clasă conține informațiile despre un document medical și oferă
 * metode pentru gestionarea acestuia. Clasa este folosită pentru serializarea/
 * deserializarea datelor din API și pentru afișarea documentelor în interfața
 * utilizator.
 * 
 * <p>Clasa include metode helper pentru determinarea iconițelor documentelor
 * și formatarea tipurilor de documente pentru afișare.</p>
 * 
 * <p>Clasa folosește anotările Gson pentru maparea câmpurilor JSON
 * la proprietățile Java.</p>
 * 
 * @author EasyMed Mobile Team
 * @version 1.0
 * @since 2024
 */
public class Document {
    /**
     * ID-ul unic al documentului în baza de date
     */
    @SerializedName("id")
    private int id;

    /**
     * Titlul documentului
     */
    @SerializedName("titlu")
    private String title;

    /**
     * Tipul documentului (analize, imagistica_medicala, observatie, etc.)
     */
    @SerializedName("tip_document")
    private String documentType;

    /**
     * Numele fișierului pe server
     */
    @SerializedName("nume_fisier")
    private String fileName;

    /**
     * Data încărcării documentului în format string
     */
    @SerializedName("data_upload")
    private String uploadDate;

    /**
     * ID-ul pacientului care deține documentul
     */
    @SerializedName("pacient_id")
    private int patientId;

    /**
     * Constructor pentru crearea unui obiect Document
     * 
     * Creează o nouă instanță de Document cu toate datele necesare.
     * 
     * @param id ID-ul unic al documentului
     * @param title Titlul documentului
     * @param documentType Tipul documentului
     * @param fileName Numele fișierului
     * @param uploadDate Data încărcării
     * @param patientId ID-ul pacientului
     */
    public Document(int id, String title, String documentType, String fileName, String uploadDate, int patientId) {
        this.id = id;
        this.title = title;
        this.documentType = documentType;
        this.fileName = fileName;
        this.uploadDate = uploadDate;
        this.patientId = patientId;
    }

    // ==================== METODE GETTER ====================
    
    /**
     * Returnează ID-ul unic al documentului
     * 
     * @return ID-ul documentului ca întreg
     */
    public int getId() { 
        return id; 
    }
    
    /**
     * Returnează titlul documentului
     * 
     * @return Titlul documentului ca String
     */
    public String getTitle() { 
        return title; 
    }
    
    /**
     * Returnează tipul documentului
     * 
     * @return Tipul documentului ca String
     */
    public String getDocumentType() { 
        return documentType; 
    }
    
    /**
     * Returnează numele fișierului
     * 
     * @return Numele fișierului ca String
     */
    public String getFileName() { 
        return fileName; 
    }
    
    /**
     * Returnează data încărcării documentului
     * 
     * @return Data încărcării în format string
     */
    public String getUploadDate() { 
        return uploadDate; 
    }
    
    /**
     * Returnează ID-ul pacientului
     * 
     * @return ID-ul pacientului ca întreg
     */
    public int getPatientId() { 
        return patientId; 
    }

    // ==================== METODE SETTER ====================
    
    /**
     * Setează ID-ul unic al documentului
     * 
     * @param id ID-ul documentului ca întreg
     */
    public void setId(int id) { 
        this.id = id; 
    }
    
    /**
     * Setează titlul documentului
     * 
     * @param title Titlul documentului ca String
     */
    public void setTitle(String title) { 
        this.title = title; 
    }
    
    /**
     * Setează tipul documentului
     * 
     * @param documentType Tipul documentului ca String
     */
    public void setDocumentType(String documentType) { 
        this.documentType = documentType; 
    }
    
    /**
     * Setează numele fișierului
     * 
     * @param fileName Numele fișierului ca String
     */
    public void setFileName(String fileName) { 
        this.fileName = fileName; 
    }
    
    /**
     * Setează data încărcării documentului
     * 
     * @param uploadDate Data încărcării ca String
     */
    public void setUploadDate(String uploadDate) { 
        this.uploadDate = uploadDate; 
    }
    
    /**
     * Setează ID-ul pacientului
     * 
     * @param patientId ID-ul pacientului ca întreg
     */
    public void setPatientId(int patientId) { 
        this.patientId = patientId; 
    }

    // ==================== METODE HELPER ====================
    
    /**
     * Returnează iconița corespunzătoare tipului de document
     * 
     * Această metodă determină iconița potrivită pentru document
     * pe baza extensiei fișierului. Iconițele sunt emoji-uri
     * care reprezintă vizual tipul de document.
     * 
     * @return Emoji-ul corespunzător tipului de document
     */
    public String getDocumentIcon() {
        if (fileName == null) return "📄";
        
        String extension = "";
        int lastDot = fileName.lastIndexOf('.');
        if (lastDot > 0) {
            extension = fileName.substring(lastDot + 1).toLowerCase();
        }

        switch (extension) {
            case "jpg":
            case "jpeg":
            case "png":
                return "🖼️"; // Iconiță pentru imagini
            case "pdf":
                return "📋"; // Iconiță pentru PDF-uri
            case "doc":
            case "docx":
                return "📝"; // Iconiță pentru documente Word
            default:
                return "📄"; // Iconiță implicită pentru alte tipuri
        }
    }

    /**
     * Returnează tipul de document formatat pentru afișare
     * 
     * Această metodă convertește codurile interne ale tipurilor
     * de documente în texte lizibile pentru utilizator.
     * 
     * @return Tipul de document formatat pentru afișare
     */
    public String getFormattedDocumentType() {
        if (documentType == null) return "Altele";
        
        switch (documentType) {
            case "analize":
                return "Analize medicale";
            case "imagistica_medicala":
                return "Imagistica medicala";
            case "observatie":
                return "Foaie de observatie";
            case "scrisori":
                return "Scrisoare medicala";
            case "externari":
                return "Bilet de externare";
            case "alte":
                return "Altele";
            default:
                return documentType;
        }
    }
} 
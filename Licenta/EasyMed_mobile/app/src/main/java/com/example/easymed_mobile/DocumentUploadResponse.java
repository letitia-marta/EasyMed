package com.example.easymed_mobile;

import com.google.gson.annotations.SerializedName;

/**
 * Clasă pentru reprezentarea răspunsului la încărcarea unui document medical
 * 
 * Această clasă conține rezultatele operației de încărcare a unui document:
 * - Statusul operației (succes/eșec)
 * - Mesajul de răspuns de la server
 * - ID-ul documentului încărcat (dacă operația a reușit)
 * 
 * <p>Clasa este folosită pentru deserializarea răspunsurilor JSON de la
 * serverul de încărcare documente.</p>
 * 
 * <p>Clasa folosește anotările Gson pentru maparea câmpurilor JSON
 * la proprietățile Java.</p>
 * 
 * @author EasyMed Mobile Team
 * @version 1.0
 * @since 2024
 */
public class DocumentUploadResponse {
    /**
     * Indică dacă încărcarea documentului a fost reușită
     */
    @SerializedName("success")
    private boolean success;

    /**
     * Mesajul de răspuns de la server
     */
    @SerializedName("message")
    private String message;

    /**
     * ID-ul documentului încărcat (null dacă operația a eșuat)
     */
    @SerializedName("document_id")
    private Integer documentId;

    /**
     * Constructor pentru crearea unui obiect DocumentUploadResponse
     * 
     * Creează o nouă instanță cu rezultatele operației de încărcare.
     * 
     * @param success Indică dacă încărcarea a fost reușită
     * @param message Mesajul de răspuns de la server
     * @param documentId ID-ul documentului încărcat
     */
    public DocumentUploadResponse(boolean success, String message, Integer documentId) {
        this.success = success;
        this.message = message;
        this.documentId = documentId;
    }

    // ==================== METODE GETTER ====================
    
    /**
     * Verifică dacă încărcarea documentului a fost reușită
     * 
     * @return true dacă încărcarea a fost reușită, false altfel
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
     * Returnează ID-ul documentului încărcat
     * 
     * @return ID-ul documentului ca Integer (poate fi null)
     */
    public Integer getDocumentId() {
        return documentId;
    }
} 
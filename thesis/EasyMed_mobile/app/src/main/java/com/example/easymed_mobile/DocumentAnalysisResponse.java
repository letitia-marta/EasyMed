package com.example.easymed_mobile;

import com.google.gson.annotations.SerializedName;

/**
 * Clasă pentru reprezentarea răspunsului de analiză AI a documentelor medicale
 * 
 * Această clasă conține rezultatele analizei AI efectuate asupra unui document
 * medical. Analiza include sugestii pentru titlu, tipul documentului detectat,
 * nivelul de încredere și informații de debug.
 * 
 * <p>Clasa este folosită pentru deserializarea răspunsurilor JSON de la
 * serverul de analiză AI și conține o clasă internă pentru informațiile
 * de debug.</p>
 * 
 * <p>Clasa folosește anotările Gson pentru maparea câmpurilor JSON
 * la proprietățile Java.</p>
 * 
 * @author EasyMed Mobile Team
 * @version 1.0
 * @since 2024
 */
public class DocumentAnalysisResponse {
    /**
     * Indică dacă analiza AI a fost executată cu succes
     */
    @SerializedName("success")
    private boolean success;

    /**
     * Titlul sugerat pentru document pe baza analizei AI
     */
    @SerializedName("suggested_title")
    private String suggestedTitle;

    /**
     * Tipul de document detectat de AI (analize, imagistica_medicala, etc.)
     */
    @SerializedName("document_type")
    private String documentType;

    /**
     * Nivelul de încredere al analizei AI (0.0 - 1.0)
     */
    @SerializedName("confidence")
    private double confidence;

    /**
     * Mesajul de eroare în caz de eșec al analizei
     */
    @SerializedName("error")
    private String error;

    /**
     * Informații de debug pentru analiza AI
     */
    @SerializedName("debug")
    private DebugInfo debug;

    /**
     * Clasă internă pentru informațiile de debug ale analizei AI
     * 
     * Această clasă conține informații detaliate despre procesul
     * de analiză AI, inclusiv textul extras și scorurile de încredere.
     */
    public static class DebugInfo {
        /**
         * Lungimea textului extras din document
         */
        @SerializedName("extracted_text_length")
        private int extractedTextLength;

        /**
         * Previzualizarea textului extras din document
         */
        @SerializedName("extracted_text_preview")
        private String extractedTextPreview;

        /**
         * Scorurile pentru diferitele tipuri de documente
         */
        @SerializedName("document_type_score")
        private Object documentTypeScore;

        /**
         * Detaliile erorilor în caz de probleme
         */
        @SerializedName("error_details")
        private String errorDetails;

        /**
         * Returnează lungimea textului extras din document
         * 
         * @return Lungimea textului ca întreg
         */
        public int getExtractedTextLength() { 
            return extractedTextLength; 
        }
        
        /**
         * Returnează previzualizarea textului extras din document
         * 
         * @return Previzualizarea textului ca String
         */
        public String getExtractedTextPreview() { 
            return extractedTextPreview; 
        }
        
        /**
         * Returnează scorurile pentru diferitele tipuri de documente
         * 
         * @return Scorurile ca Object (poate fi Map sau alt tip)
         */
        public Object getDocumentTypeScore() { 
            return documentTypeScore; 
        }
        
        /**
         * Returnează detaliile erorilor în caz de probleme
         * 
         * @return Detaliile erorilor ca String
         */
        public String getErrorDetails() { 
            return errorDetails; 
        }
    }

    /**
     * Constructor pentru crearea unui obiect DocumentAnalysisResponse
     * 
     * Creează o nouă instanță cu toate datele rezultate din analiza AI.
     * 
     * @param success Indică dacă analiza a fost reușită
     * @param suggestedTitle Titlul sugerat pentru document
     * @param documentType Tipul de document detectat
     * @param confidence Nivelul de încredere al analizei
     * @param error Mesajul de eroare (dacă există)
     * @param debug Informațiile de debug
     */
    public DocumentAnalysisResponse(boolean success, String suggestedTitle, String documentType, 
                                  double confidence, String error, DebugInfo debug) {
        this.success = success;
        this.suggestedTitle = suggestedTitle;
        this.documentType = documentType;
        this.confidence = confidence;
        this.error = error;
        this.debug = debug;
    }

    // ==================== METODE GETTER ====================
    
    /**
     * Verifică dacă analiza AI a fost executată cu succes
     * 
     * @return true dacă analiza a fost reușită, false altfel
     */
    public boolean isSuccess() { 
        return success; 
    }
    
    /**
     * Returnează titlul sugerat pentru document
     * 
     * @return Titlul sugerat ca String
     */
    public String getSuggestedTitle() { 
        return suggestedTitle; 
    }
    
    /**
     * Returnează tipul de document detectat de AI
     * 
     * @return Tipul de document ca String
     */
    public String getDocumentType() { 
        return documentType; 
    }
    
    /**
     * Returnează nivelul de încredere al analizei AI
     * 
     * @return Nivelul de încredere ca double (0.0 - 1.0)
     */
    public double getConfidence() { 
        return confidence; 
    }
    
    /**
     * Returnează mesajul de eroare în caz de eșec
     * 
     * @return Mesajul de eroare ca String
     */
    public String getError() { 
        return error; 
    }
    
    /**
     * Returnează informațiile de debug ale analizei
     * 
     * @return Informațiile de debug ca DebugInfo
     */
    public DebugInfo getDebug() { 
        return debug; 
    }
} 
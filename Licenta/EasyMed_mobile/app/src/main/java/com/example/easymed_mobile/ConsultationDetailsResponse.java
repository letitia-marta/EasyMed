package com.example.easymed_mobile;

import com.google.gson.annotations.SerializedName;
import java.util.List;

/**
 * Clasă pentru reprezentarea răspunsului cu detaliile complete ale unei consultații în EasyMed
 * 
 * Această clasă conține toate informațiile detaliate despre o consultație:
 * - Detaliile consultației (simptome, diagnostic, etc.)
 * - Biletele de trimitere
 * - Biletele de investigații
 * - Rețetele medicale
 * - Informațiile despre pacient și medic
 * 
 * <p>Clasa este folosită pentru deserializarea răspunsurilor JSON de la
 * serverul de detalii consultații și conține multiple clase interne
 * pentru organizarea datelor.</p>
 * 
 * <p>Clasa folosește anotările Gson pentru maparea câmpurilor JSON
 * la proprietățile Java.</p>
 * 
 * @author EasyMed Mobile Team
 * @version 1.0
 * @since 2024
 */
public class ConsultationDetailsResponse {
    /**
     * Detaliile consultației
     */
    @SerializedName("consultation")
    private ConsultationDetails consultation;
    
    /**
     * Lista biletelor de trimitere
     */
    @SerializedName("referral_tickets")
    private List<ReferralTicket> referralTickets;
    
    /**
     * Lista biletelor de investigații
     */
    @SerializedName("investigation_tickets")
    private List<InvestigationTicket> investigationTickets;
    
    /**
     * Lista rețetelor medicale
     */
    @SerializedName("prescriptions")
    private List<Prescription> prescriptions;
    
    /**
     * Mesajul de eroare în caz de eșec
     */
    @SerializedName("error")
    private String error;

    /**
     * Returnează detaliile consultației
     * 
     * @return Detaliile consultației
     */
    public ConsultationDetails getConsultation() { return consultation; }
    
    /**
     * Returnează lista biletelor de trimitere
     * 
     * @return Lista biletelor de trimitere
     */
    public List<ReferralTicket> getReferralTickets() { return referralTickets; }
    
    /**
     * Returnează lista biletelor de investigații
     * 
     * @return Lista biletelor de investigații
     */
    public List<InvestigationTicket> getInvestigationTickets() { return investigationTickets; }
    
    /**
     * Returnează lista rețetelor medicale
     * 
     * @return Lista rețetelor medicale
     */
    public List<Prescription> getPrescriptions() { return prescriptions; }
    
    /**
     * Returnează mesajul de eroare
     * 
     * @return Mesajul de eroare ca String
     */
    public String getError() { return error; }
    
    /**
     * Verifică dacă cererea a fost reușită
     * 
     * @return true dacă nu există eroare, false altfel
     */
    public boolean isSuccess() { return error == null; }

    /**
     * Clasă internă pentru detaliile consultației
     * 
     * Conține informațiile de bază despre consultație:
     * data, ora, simptome, diagnostic și informații despre
     * pacient și medic.
     */
    public static class ConsultationDetails {
        /**
         * ID-ul unic al consultației
         */
        @SerializedName("id")
        private int id;
        
        /**
         * Data consultației
         */
        @SerializedName("date")
        private String date;
        
        /**
         * Ora consultației
         */
        @SerializedName("time")
        private String time;
        
        /**
         * Simptomele pacientului
         */
        @SerializedName("symptoms")
        private String symptoms;
        
        /**
         * Codul diagnosticului
         */
        @SerializedName("diagnosis_code")
        private String diagnosisCode;
        
        /**
         * Numele diagnosticului
         */
        @SerializedName("diagnosis_name")
        private String diagnosisName;
        
        /**
         * Informațiile despre pacient
         */
        @SerializedName("patient")
        private PatientInfo patient;
        
        /**
         * Informațiile despre medic
         */
        @SerializedName("doctor")
        private DoctorInfo doctor;

        /**
         * Returnează ID-ul consultației
         * 
         * @return ID-ul consultației ca întreg
         */
        public int getId() { return id; }
        
        /**
         * Returnează data consultației
         * 
         * @return Data consultației ca String
         */
        public String getDate() { return date; }
        
        /**
         * Returnează ora consultației
         * 
         * @return Ora consultației ca String
         */
        public String getTime() { return time; }
        
        /**
         * Returnează simptomele pacientului
         * 
         * @return Simptomele ca String
         */
        public String getSymptoms() { return symptoms; }
        
        /**
         * Returnează codul diagnosticului
         * 
         * @return Codul diagnosticului ca String
         */
        public String getDiagnosisCode() { return diagnosisCode; }
        
        /**
         * Returnează numele diagnosticului
         * 
         * @return Numele diagnosticului ca String
         */
        public String getDiagnosisName() { return diagnosisName; }
        
        /**
         * Returnează informațiile despre pacient
         * 
         * @return Informațiile despre pacient
         */
        public PatientInfo getPatient() { return patient; }
        
        /**
         * Returnează informațiile despre medic
         * 
         * @return Informațiile despre medic
         */
        public DoctorInfo getDoctor() { return doctor; }
    }

    /**
     * Clasă internă pentru informațiile despre pacient
     * 
     * Conține informațiile de bază despre pacient:
     * numele și CNP-ul.
     */
    public static class PatientInfo {
        /**
         * Numele pacientului
         */
        @SerializedName("name")
        private String name;
        
        /**
         * CNP-ul pacientului
         */
        @SerializedName("cnp")
        private String cnp;

        /**
         * Returnează numele pacientului
         * 
         * @return Numele pacientului ca String
         */
        public String getName() { return name; }
        
        /**
         * Returnează CNP-ul pacientului
         * 
         * @return CNP-ul pacientului ca String
         */
        public String getCnp() { return cnp; }
    }

    /**
     * Clasă internă pentru informațiile despre medic
     * 
     * Conține informațiile de bază despre medic:
     * numele, specializarea și codul de ștampilă.
     */
    public static class DoctorInfo {
        /**
         * Numele medicului
         */
        @SerializedName("name")
        private String name;
        
        /**
         * Specializarea medicului
         */
        @SerializedName("specialty")
        private String specialty;
        
        /**
         * Codul de ștampilă al medicului
         */
        @SerializedName("stamp_code")
        private String stampCode;

        /**
         * Returnează numele medicului
         * 
         * @return Numele medicului ca String
         */
        public String getName() { return name; }
        
        /**
         * Returnează specializarea medicului
         * 
         * @return Specializarea medicului ca String
         */
        public String getSpecialty() { return specialty; }
        
        /**
         * Returnează codul de ștampilă al medicului
         * 
         * @return Codul de ștampilă ca String
         */
        public String getStampCode() { return stampCode; }
    }

    /**
     * Clasă internă pentru biletele de trimitere
     * 
     * Conține informațiile despre un bilet de trimitere:
     * codul și specializarea.
     */
    public static class ReferralTicket {
        /**
         * Codul biletului de trimitere
         */
        @SerializedName("Cod")
        private String code;
        
        /**
         * Specializarea pentru care se face trimiterea
         */
        @SerializedName("Specializare")
        private String specialty;

        /**
         * Returnează codul biletului de trimitere
         * 
         * @return Codul biletului ca String
         */
        public String getCode() { return code; }
        
        /**
         * Returnează specializarea pentru trimitere
         * 
         * @return Specializarea ca String
         */
        public String getSpecialty() { return specialty; }
    }

    /**
     * Clasă internă pentru biletele de investigații
     * 
     * Conține informațiile despre un bilet de investigații:
     * codul și numele investigațiilor.
     */
    public static class InvestigationTicket {
        /**
         * Codul biletului de investigații
         */
        @SerializedName("CodBilet")
        private String code;
        
        /**
         * Numele investigațiilor
         */
        @SerializedName("nume_investigatii")
        private String investigations;

        /**
         * Returnează codul biletului de investigații
         * 
         * @return Codul biletului ca String
         */
        public String getCode() { return code; }
        
        /**
         * Returnează numele investigațiilor
         * 
         * @return Numele investigațiilor ca String
         */
        public String getInvestigations() { return investigations; }
    }

    /**
     * Clasă internă pentru rețetele medicale
     * 
     * Conține informațiile despre o rețetă medicală:
     * codul, medicamentul, forma farmaceutică, cantitatea și durata.
     */
    public static class Prescription {
        /**
         * Codul rețetei
         */
        @SerializedName("Cod")
        private String code;
        
        /**
         * Numele medicamentului
         */
        @SerializedName("Medicamente")
        private String medication;
        
        /**
         * Forma farmaceutică
         */
        @SerializedName("FormaFarmaceutica")
        private String pharmaceuticalForm;
        
        /**
         * Cantitatea prescrisă
         */
        @SerializedName("Cantitate")
        private String quantity;
        
        /**
         * Durata tratamentului
         */
        @SerializedName("Durata")
        private String duration;

        /**
         * Returnează codul rețetei
         * 
         * @return Codul rețetei ca String
         */
        public String getCode() { return code; }
        
        /**
         * Returnează numele medicamentului
         * 
         * @return Numele medicamentului ca String
         */
        public String getMedication() { return medication; }
        
        /**
         * Returnează forma farmaceutică
         * 
         * @return Forma farmaceutică ca String
         */
        public String getPharmaceuticalForm() { return pharmaceuticalForm; }
        
        /**
         * Returnează cantitatea prescrisă
         * 
         * @return Cantitatea ca String
         */
        public String getQuantity() { return quantity; }
        
        /**
         * Returnează durata tratamentului
         * 
         * @return Durata ca String
         */
        public String getDuration() { return duration; }
    }
} 
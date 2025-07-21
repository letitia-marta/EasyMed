package com.example.easymed_mobile;

import com.google.gson.annotations.SerializedName;

/**
 * Clasă pentru reprezentarea unei programări medicale în aplicația EasyMed
 * 
 * Această clasă conține informațiile despre o programare medicală și oferă
 * metode pentru accesarea și modificarea acestor date. Clasa este folosită
 * pentru serializarea/deserializarea datelor din API și pentru gestionarea
 * programărilor în interfața utilizator.
 * 
 * <p>Clasa folosește anotările Gson pentru maparea câmpurilor JSON
 * la proprietățile Java.</p>
 * 
 * @author EasyMed Mobile Team
 * @version 1.0
 * @since 2024
 */
public class Appointment {
    /**
     * ID-ul unic al programării în baza de date
     */
    @SerializedName("id")
    private int id;
    
    /**
     * ID-ul pacientului care are programarea
     */
    @SerializedName("patient_id")
    private int patientId;
    
    /**
     * ID-ul medicului care va efectua consultația
     */
    @SerializedName("doctor_id")
    private int doctorId;
    
    /**
     * Data programării în format string
     */
    @SerializedName("date")
    private String date;
    
    /**
     * Ora programării în format string
     */
    @SerializedName("time_slot")
    private String timeSlot;
    
    /**
     * Tipul de consultație (ex: consultație generală, control, etc.)
     */
    @SerializedName("consultation_type")
    private String consultationType;
    
    /**
     * Numele complet al medicului (nume + prenume)
     */
    @SerializedName("doctor_name")
    private String doctorName;
    
    /**
     * Specializarea medicului
     */
    @SerializedName("specialty")
    private String specialty;
    
    /**
     * Constructor pentru crearea unui obiect Appointment
     * 
     * Creează o nouă instanță de Appointment cu toate datele necesare.
     * 
     * @param id ID-ul unic al programării
     * @param patientId ID-ul pacientului
     * @param doctorId ID-ul medicului
     * @param date Data programării
     * @param timeSlot Ora programării
     * @param consultationType Tipul de consultație
     */
    public Appointment(int id, int patientId, int doctorId, String date, String timeSlot, String consultationType) {
        this.id = id;
        this.patientId = patientId;
        this.doctorId = doctorId;
        this.date = date;
        this.timeSlot = timeSlot;
        this.consultationType = consultationType;
    }
    
    // ==================== METODE GETTER ====================
    
    /**
     * Returnează ID-ul unic al programării
     * 
     * @return ID-ul programării ca întreg
     */
    public int getId() { 
        return id; 
    }
    
    /**
     * Returnează ID-ul pacientului
     * 
     * @return ID-ul pacientului ca întreg
     */
    public int getPatientId() { 
        return patientId; 
    }
    
    /**
     * Returnează ID-ul medicului
     * 
     * @return ID-ul medicului ca întreg
     */
    public int getDoctorId() { 
        return doctorId; 
    }
    
    /**
     * Returnează data programării
     * 
     * @return Data programării în format string
     */
    public String getDate() { 
        return date; 
    }
    
    /**
     * Returnează ora programării
     * 
     * @return Ora programării în format string
     */
    public String getTimeSlot() { 
        return timeSlot; 
    }
    
    /**
     * Returnează tipul de consultație
     * 
     * @return Tipul de consultație ca String
     */
    public String getConsultationType() { 
        return consultationType; 
    }
    
    /**
     * Returnează numele complet al medicului
     * 
     * @return Numele medicului ca String
     */
    public String getDoctorName() { 
        return doctorName; 
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
     * Returnează ID-ul medicului pentru compatibilitatea cu API-ul
     * 
     * Această metodă este un alias pentru getDoctorId() și este folosită
     * pentru a menține compatibilitatea cu API-ul care folosește numele
     * de câmp "medic_id".
     * 
     * @return ID-ul medicului ca întreg
     */
    public int getMedicId() { 
        return doctorId; 
    }
    
    // ==================== METODE SETTER ====================
    
    /**
     * Setează ID-ul unic al programării
     * 
     * @param id ID-ul programării ca întreg
     */
    public void setId(int id) { 
        this.id = id; 
    }
    
    /**
     * Setează ID-ul pacientului
     * 
     * @param patientId ID-ul pacientului ca întreg
     */
    public void setPatientId(int patientId) { 
        this.patientId = patientId; 
    }
    
    /**
     * Setează ID-ul medicului
     * 
     * @param doctorId ID-ul medicului ca întreg
     */
    public void setDoctorId(int doctorId) { 
        this.doctorId = doctorId; 
    }
    
    /**
     * Setează data programării
     * 
     * @param date Data programării ca String
     */
    public void setDate(String date) { 
        this.date = date; 
    }
    
    /**
     * Setează ora programării
     * 
     * @param timeSlot Ora programării ca String
     */
    public void setTimeSlot(String timeSlot) { 
        this.timeSlot = timeSlot; 
    }
    
    /**
     * Setează tipul de consultație
     * 
     * @param consultationType Tipul de consultație ca String
     */
    public void setConsultationType(String consultationType) { 
        this.consultationType = consultationType; 
    }
} 
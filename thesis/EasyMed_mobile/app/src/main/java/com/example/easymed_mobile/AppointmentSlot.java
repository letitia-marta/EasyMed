package com.example.easymed_mobile;

/**
 * Clasă pentru reprezentarea unui slot de programare în aplicația EasyMed
 *
 * Această clasă conține informațiile despre o programare:
 * - ID-ul programării
 * - ID-ul pacientului
 * - ID-ul medicului
 * - Data programării
 * - Ora/slotul programării
 * - Tipul consultației
 *
 * <p>Clasa este folosită pentru serializarea/deserializarea datelor
 * despre programări între client și server.</p>
 *
 * @author EasyMed Mobile Team
 * @version 1.0
 * @since 2024
 */
public class AppointmentSlot {
    /**
     * ID-ul unic al programării în baza de date
     */
    public int id;
    /**
     * ID-ul pacientului asociat programării
     */
    public int patient_id;
    /**
     * ID-ul medicului asociat programării
     */
    public int doctor_id;
    /**
     * Data programării (format yyyy-MM-dd)
     */
    public String date;
    /**
     * Ora/slotul programării (ex: "09:00", "14:30")
     */
    public String time_slot;
    /**
     * Tipul consultației (ex: "control", "consultație de specialitate")
     */
    public String consultation_type;

    /**
     * Constructor implicit pentru serializare/deserializare
     */
    public AppointmentSlot() {}

    /**
     * Returnează ora/slotul programării
     *
     * @return Ora/slotul programării ca String
     */
    public String getTimeSlot() { return time_slot; }
} 
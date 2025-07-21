package com.example.easymed_mobile;

/**
 * Clasă pentru reprezentarea unui slot de timp pentru programări în aplicația EasyMed
 * 
 * Această clasă conține informațiile despre un slot de timp disponibil
 * pentru programări: ora slotului și statusul său (disponibil, ocupat, trecut).
 * 
 * <p>Clasa include o enumerare pentru statusurile posibile ale sloturilor
 * și metode helper pentru verificarea statusului.</p>
 * 
 * @author EasyMed Mobile Team
 * @version 1.0
 * @since 2024
 */
public class TimeSlot {
    /**
     * Enumerare pentru statusurile posibile ale unui slot de timp
     */
    public enum Status {
        /**
         * Slot disponibil pentru programare
         */
        AVAILABLE,
        
        /**
         * Slot ocupat de o programare existentă
         */
        OCCUPIED,
        
        /**
         * Slot din trecut (nu mai poate fi programat)
         */
        PAST
    }
    
    /**
     * Ora slotului în format string (ex: "09:00", "14:30")
     */
    private String time;
    
    /**
     * Statusul curent al slotului
     */
    private Status status;
    
    /**
     * Constructor pentru crearea unui obiect TimeSlot
     * 
     * Creează o nouă instanță de TimeSlot cu ora și statusul specificat.
     * 
     * @param time Ora slotului
     * @param status Statusul slotului
     */
    public TimeSlot(String time, Status status) {
        this.time = time;
        this.status = status;
    }
    
    // ==================== METODE GETTER ====================
    
    /**
     * Returnează ora slotului
     * 
     * @return Ora slotului în format string
     */
    public String getTime() {
        return time;
    }
    
    /**
     * Returnează statusul slotului
     * 
     * @return Statusul slotului ca enum Status
     */
    public Status getStatus() {
        return status;
    }
    
    // ==================== METODE HELPER ====================
    
    /**
     * Verifică dacă slotul este disponibil pentru programare
     * 
     * @return true dacă slotul este disponibil, false altfel
     */
    public boolean isAvailable() {
        return status == Status.AVAILABLE;
    }
    
    /**
     * Verifică dacă slotul este ocupat de o programare existentă
     * 
     * @return true dacă slotul este ocupat, false altfel
     */
    public boolean isOccupied() {
        return status == Status.OCCUPIED;
    }
    
    /**
     * Verifică dacă slotul este din trecut
     * 
     * @return true dacă slotul este din trecut, false altfel
     */
    public boolean isPast() {
        return status == Status.PAST;
    }
} 
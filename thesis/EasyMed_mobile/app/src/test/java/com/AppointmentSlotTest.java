package com.example.easymed_mobile;

import org.junit.Test;
import static org.junit.Assert.*;

public class AppointmentSlotTest {
    @Test
    public void publicFields_andGetTimeSlot_workCorrectly() {
        AppointmentSlot slot = new AppointmentSlot();
        slot.id = 1;
        slot.patient_id = 2;
        slot.doctor_id = 3;
        slot.date = "2024-06-01";
        slot.time_slot = "10:30";
        slot.consultation_type = "control";
        assertEquals(1, slot.id);
        assertEquals(2, slot.patient_id);
        assertEquals(3, slot.doctor_id);
        assertEquals("2024-06-01", slot.date);
        assertEquals("10:30", slot.time_slot);
        assertEquals("control", slot.consultation_type);
        assertEquals("10:30", slot.getTimeSlot());
    }
} 
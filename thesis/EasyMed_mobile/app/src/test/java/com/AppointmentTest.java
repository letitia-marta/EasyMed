package com.example.easymed_mobile;

import org.junit.Test;
import static org.junit.Assert.*;

public class AppointmentTest {
    @Test
    public void constructor_andGetters_workCorrectly() {
        Appointment appt = new Appointment(1, 2, 3, "2024-01-01", "10:00", "Consultation");
        assertEquals(1, appt.getId());
        assertEquals(2, appt.getPatientId());
        assertEquals(3, appt.getDoctorId());
        assertEquals("2024-01-01", appt.getDate());
        assertEquals("10:00", appt.getTimeSlot());
        assertEquals("Consultation", appt.getConsultationType());
        assertEquals(3, appt.getMedicId()); // alias
    }

    @Test
    public void setters_updateFieldsCorrectly() {
        Appointment appt = new Appointment(0, 0, 0, "", "", "");
        appt.setId(10);
        appt.setPatientId(20);
        appt.setDoctorId(30);
        appt.setDate("2024-12-31");
        appt.setTimeSlot("15:30");
        appt.setConsultationType("Follow-up");
        assertEquals(10, appt.getId());
        assertEquals(20, appt.getPatientId());
        assertEquals(30, appt.getDoctorId());
        assertEquals("2024-12-31", appt.getDate());
        assertEquals("15:30", appt.getTimeSlot());
        assertEquals("Follow-up", appt.getConsultationType());
    }
} 
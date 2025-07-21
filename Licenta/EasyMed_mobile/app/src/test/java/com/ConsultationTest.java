package com.example.easymed_mobile;

import org.junit.Test;
import static org.junit.Assert.*;

public class ConsultationTest {
    @Test
    public void getters_workCorrectly() {
        // Use reflection to set private fields for testing
        Consultation c = new Consultation();
        try {
            java.lang.reflect.Field id = Consultation.class.getDeclaredField("id");
            java.lang.reflect.Field date = Consultation.class.getDeclaredField("date");
            java.lang.reflect.Field time = Consultation.class.getDeclaredField("time");
            java.lang.reflect.Field doctor_name = Consultation.class.getDeclaredField("doctor_name");
            java.lang.reflect.Field specialty = Consultation.class.getDeclaredField("specialty");
            java.lang.reflect.Field diagnosis = Consultation.class.getDeclaredField("diagnosis");
            id.setAccessible(true); date.setAccessible(true); time.setAccessible(true);
            doctor_name.setAccessible(true); specialty.setAccessible(true); diagnosis.setAccessible(true);
            id.set(c, 42);
            date.set(c, "2024-05-01");
            time.set(c, "13:00");
            doctor_name.set(c, "Dr. House");
            specialty.set(c, "Diagnostics");
            diagnosis.set(c, "Lupus");
            assertEquals(42, c.getId());
            assertEquals("2024-05-01", c.getDate());
            assertEquals("13:00", c.getTime());
            assertEquals("Dr. House", c.getDoctor_name());
            assertEquals("Diagnostics", c.getSpecialty());
            assertEquals("Lupus", c.getDiagnosis());
        } catch (Exception e) {
            fail("Reflection failed: " + e.getMessage());
        }
    }
} 
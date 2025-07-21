package com.example.easymed_mobile;

import org.junit.Test;
import static org.junit.Assert.*;
import java.util.Arrays;
import java.util.Collections;
import java.util.List;

public class DoctorsResponseTest {
    @Test
    public void isSuccess_returnsCorrectValue() {
        DoctorsResponse resp = new DoctorsResponse();
        // Using reflection to set private field for test
        try {
            java.lang.reflect.Field f = DoctorsResponse.class.getDeclaredField("success");
            f.setAccessible(true);
            f.set(resp, true);
            assertTrue(resp.isSuccess());
            f.set(resp, false);
            assertFalse(resp.isSuccess());
        } catch (Exception e) {
            fail("Reflection failed: " + e.getMessage());
        }
    }

    @Test
    public void getDoctors_returnsCorrectList() {
        DoctorsResponse resp = new DoctorsResponse();
        try {
            java.lang.reflect.Field f = DoctorsResponse.class.getDeclaredField("doctors");
            f.setAccessible(true);
            List<Doctor> doctors = Arrays.asList(new Doctor(), new Doctor());
            f.set(resp, doctors);
            assertEquals(2, resp.getDoctors().size());
        } catch (Exception e) {
            fail("Reflection failed: " + e.getMessage());
        }
    }

    @Test
    public void getError_returnsCorrectValue() {
        DoctorsResponse resp = new DoctorsResponse();
        try {
            java.lang.reflect.Field f = DoctorsResponse.class.getDeclaredField("error");
            f.setAccessible(true);
            f.set(resp, "Some error");
            assertEquals("Some error", resp.getError());
        } catch (Exception e) {
            fail("Reflection failed: " + e.getMessage());
        }
    }
} 
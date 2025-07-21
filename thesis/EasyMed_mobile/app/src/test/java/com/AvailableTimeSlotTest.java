package com.example.easymed_mobile;

import org.junit.Test;
import static org.junit.Assert.*;

public class AvailableTimeSlotTest {
    @Test
    public void gettersAndSetters_workCorrectly() {
        AvailableTimeSlot slot = new AvailableTimeSlot();
        slot.setTime("09:00");
        slot.setAvailable(true);
        slot.setIsPast(false);
        assertEquals("09:00", slot.getTime());
        assertTrue(slot.isAvailable());
        assertFalse(slot.isPast());
        slot.setAvailable(false);
        slot.setIsPast(true);
        assertFalse(slot.isAvailable());
        assertTrue(slot.isPast());
    }
} 
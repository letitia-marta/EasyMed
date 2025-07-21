package com.example.easymed_mobile;

import org.junit.Test;
import static org.junit.Assert.*;

public class UserTest {
    @Test
    public void userFields_areSetAndAccessible() {
        User user = new User();
        user.id = 123;
        user.email = "test@example.com";

        assertEquals(123, user.id);
        assertEquals("test@example.com", user.email);
    }
} 
package com.example.easymed_mobile;

import org.junit.Test;
import static org.junit.Assert.*;

public class DocumentTest {
    @Test
    public void constructor_andGetters_workCorrectly() {
        Document doc = new Document(1, "Title", "pdf", "file.pdf", "2024-01-01", 2);
        assertEquals(1, doc.getId());
        assertEquals("Title", doc.getTitle());
        assertEquals("pdf", doc.getDocumentType());
        assertEquals("file.pdf", doc.getFileName());
        assertEquals("2024-01-01", doc.getUploadDate());
        assertEquals(2, doc.getPatientId());
    }

    @Test
    public void setters_updateFieldsCorrectly() {
        Document doc = new Document(0, "", "", "", "", 0);
        doc.setId(10);
        doc.setTitle("New Title");
        doc.setDocumentType("docx");
        doc.setFileName("report.docx");
        doc.setUploadDate("2024-12-31");
        doc.setPatientId(20);
        assertEquals(10, doc.getId());
        assertEquals("New Title", doc.getTitle());
        assertEquals("docx", doc.getDocumentType());
        assertEquals("report.docx", doc.getFileName());
        assertEquals("2024-12-31", doc.getUploadDate());
        assertEquals(20, doc.getPatientId());
    }

    @Test
    public void getDocumentIcon_returnsCorrectIcon() {
        Document img = new Document(1, "", "", "pic.jpg", "", 0);
        Document pdf = new Document(1, "", "", "file.pdf", "", 0);
        Document docx = new Document(1, "", "", "doc.docx", "", 0);
        Document unknown = new Document(1, "", "", "archive.zip", "", 0);
        Document nullFile = new Document(1, "", "", null, "", 0);
        assertEquals("\uD83D\uDDBC\uFE0F", img.getDocumentIcon());
        assertEquals("\uD83D\uDCCB", pdf.getDocumentIcon());
        assertEquals("\uD83D\uDCDD", docx.getDocumentIcon());
        assertEquals("\uD83D\uDCC4", unknown.getDocumentIcon());
        assertEquals("\uD83D\uDCC4", nullFile.getDocumentIcon());
    }
} 
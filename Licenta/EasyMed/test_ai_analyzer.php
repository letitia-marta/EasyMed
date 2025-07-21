<?php
/**
 * Pagină de test pentru analizatorul AI de documente medicale
 * 
 * Această pagină oferă o interfață pentru testarea funcționalității AI:
 * - Permite încărcarea documentelor medicale pentru testare
 * - Afișează rezultatele analizei AI în timp real
 * - Include informații despre termenii medicali recunoscuți
 * - Oferă debugging detaliat pentru dezvoltatori
 * - Suportă drag & drop pentru încărcarea fișierelor
 * - Afișează scorurile de încredere și clasificarea documentelor
 * 
 * @author EasyMed System
 * @version 1.0
 * @since 2024
 */

require_once 'ai_document_analyzer.php';

?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test AI Document Analyzer - EasyMed</title>
    <style>
        /* Stilizarea generală a paginii */
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        
        /* Container-ul principal pentru test */
        .test-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        /* Zona de încărcare cu drag & drop */
        .upload-area {
            border: 2px dashed #ccc;
            padding: 40px;
            text-align: center;
            margin: 20px 0;
            border-radius: 8px;
        }
        
        .upload-area.dragover {
            border-color: #5cf9c8;
            background: #f0fffd;
        }
        
        /* Stilizarea rezultatelor */
        .result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
            background: #f8f9fa;
            border-left: 4px solid #5cf9c8;
        }
        
        .error {
            border-left-color: #dc3545;
            background: #f8d7da;
        }
        
        /* Informații de debugging */
        .debug-info {
            background: #e9ecef;
            padding: 10px;
            border-radius: 4px;
            margin-top: 10px;
            font-family: monospace;
            font-size: 12px;
            white-space: pre-wrap;
        }
        
        /* Stilizarea butoanelor */
        button {
            background: #5cf9c8;
            color: black;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            margin: 5px;
        }
        
        button:hover {
            background: #4dd4b0;
        }
        
        button:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <!-- Container-ul principal pentru test -->
    <div class="test-container">
        <h1>🤖 Test AI Document Analyzer</h1>
        <p>Încărcați un document medical pentru a testa analiza AI.</p>
        
        <!-- Zona de încărcare cu drag & drop -->
        <div class="upload-area" id="uploadArea">
            <p>📁 Trageți un fișier aici sau faceți click pentru a selecta</p>
            <input type="file" id="fileInput" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" style="display: none;">
            <button onclick="document.getElementById('fileInput').click()">Selectează Fișier</button>
        </div>
        
        <!-- Zona pentru afișarea rezultatelor -->
        <div id="result" style="display: none;"></div>
        
        <!-- Informații despre funcționalitatea AI -->
        <div style="margin-top: 30px;">
            <h3>📋 Informații despre AI Analyzer:</h3>
            <ul>
                <li><strong>PDF:</strong> Folosește pdftotext pentru extragerea textului</li>
                <li><strong>Imagini:</strong> Folosește Tesseract OCR pentru recunoașterea textului</li>
                <li><strong>Word:</strong> Extrage text din fișierele .docx</li>
                <li><strong>Analiză:</strong> Caută termeni medicali românești</li>
                <li><strong>Clasificare:</strong> Detectează automat tipul documentului</li>
            </ul>
            
            <!-- Lista termenilor medicali recunoscuți -->
            <h4>Termeni medicali recunoscuți:</h4>
            <div style="font-size: 12px; color: #666;">
                <strong>Analize:</strong> hemoglobina, glicemie, colesterol, leucocite, etc.<br>
                <strong>Radiografii:</strong> radiografie, x-ray, fractura, pulmonar, etc.<br>
                <strong>Ecografii:</strong> ecografie, ultrasunet, fetus, cardiac, etc.<br>
                <strong>Fise de observație:</strong> fisa, observatie, internare, spital, etc.<br>
                <strong>Scrisori:</strong> scrisoare, aviz, consultatie, certificat, etc.<br>
                <strong>Bilete de externare:</strong> externare, bilet, sumar, concluzie, etc.
            </div>
        </div>
    </div>

    <!-- Script-ul pentru funcționalitatea de test -->
    <script>
        // Obține referințele la elementele DOM
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('fileInput');
        const resultDiv = document.getElementById('result');

        // Event listener pentru drag over
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        // Event listener pentru drag leave
        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        // Event listener pentru drop
        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                analyzeFile(files[0]);
            }
        });

        // Event listener pentru selectarea fișierului
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                analyzeFile(e.target.files[0]);
            }
        });

        /**
         * Funcția principală pentru analizarea fișierului
         * @param {File} file - Fișierul de analizat
         */
        function analyzeFile(file) {
            // Ascunde rezultatele anterioare
            resultDiv.style.display = 'none';
            
            // Pregătește datele pentru trimitere
            const formData = new FormData();
            formData.append('document_file', file);
        
            // Afișează mesajul de încărcare
            resultDiv.innerHTML = '<div class="result"><p>🤖 Analizez documentul...</p></div>';
            resultDiv.style.display = 'block';
            
            // Trimite cererea către AI analyzer
            fetch('ai_document_analyzer.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                let html = '<div class="result' + (data.success ? '' : ' error') + '">';
                
                if (data.success) {
                    // Afișează rezultatele de succes
                    html += `
                        <h3>✅ Analiză Completă</h3>
                        <p><strong>Titlu sugerat:</strong> ${data.suggested_title}</p>
                        <p><strong>Tip document:</strong> ${data.document_type}</p>
                        <p><strong>Încredere:</strong> ${(data.confidence * 100).toFixed(1)}%</p>
                    `;
                    
                    // Afișează informațiile de debugging dacă sunt disponibile
                    if (data.debug) {
                        html += `
                            <div class="debug-info">
                                <strong>Debug Info:</strong>
                                Lungime text: ${data.debug.extracted_text_length} caractere
                                Previzualizare text: ${data.debug.extracted_text_preview}
                                
                                Scoruri tip document:
                                ${JSON.stringify(data.debug.document_type_score, null, 2)}
                            </div>
                        `;
                    }
                } else {
                    // Afișează erorile
                    html += `
                        <h3>❌ Eroare la Analiză</h3>
                        <p><strong>Eroare:</strong> ${data.error}</p>
                        <p><strong>Titlu fallback:</strong> ${data.suggested_title}</p>
                    `;
                    
                    if (data.debug) {
                        html += `<div class="debug-info">${data.debug.error_details}</div>`;
                    }
                }
                
                html += '</div>';
                resultDiv.innerHTML = html;
            })
            .catch(error => {
                // Afișează erorile de rețea
                resultDiv.innerHTML = `
                    <div class="result error">
                        <h3>❌ Eroare de Rețea</h3>
                        <p>Eroare: ${error.message}</p>
                    </div>
                `;
            });
        }
    </script>
</body>
</html> 
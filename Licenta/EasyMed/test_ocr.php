<?php
require_once 'ai_document_analyzer.php';

?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test OCR - EasyMed</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .test-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
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
        .debug-info {
            background: #e9ecef;
            padding: 10px;
            border-radius: 4px;
            margin-top: 10px;
            font-family: monospace;
            font-size: 12px;
            white-space: pre-wrap;
        }
        .text-preview {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 15px;
            border-radius: 4px;
            margin-top: 10px;
            max-height: 300px;
            overflow-y: auto;
        }
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
    <div class="test-container">
        <h1>🔍 Test OCR Text Extraction</h1>
        <p>Încărcați un document pentru a testa extragerea textului cu OCR.</p>
        
        <div class="upload-area" id="uploadArea">
            <p>📁 Trageți un fișier aici sau faceți click pentru a selecta</p>
            <input type="file" id="fileInput" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" style="display: none;">
            <button onclick="document.getElementById('fileInput').click()">Selectează Fișier</button>
        </div>
        
        <div id="result" style="display: none;"></div>
        
        <div style="margin-top: 30px;">
            <h3>📋 Testare de Baza:</h3>
            <button onclick="testSimple()">Testează Conexiunea</button>
            <div id="simpleResult" style="margin-top: 10px;"></div>
            
            <button onclick="testHardcoded()">Testează Răspuns Hardcodat</button>
            <div id="hardcodedResult" style="margin-top: 10px;"></div>
            
            <h3>📋 Informații despre OCR:</h3>
            <ul>
                <li><strong>PDF:</strong> Folosește pdftotext pentru extragerea textului</li>
                <li><strong>Imagini:</strong> Folosește Tesseract OCR pentru recunoașterea textului</li>
                <li><strong>Word:</strong> Extrage text din fișierele .docx</li>
                <li><strong>Debug:</strong> Afișează informații detaliate despre procesul de extragere</li>
            </ul>
            
            <h4>Testare Tesseract:</h4>
            <button onclick="testTesseract()">Testează Tesseract</button>
            <div id="tesseractResult" style="margin-top: 10px;"></div>
        </div>
    </div>

    <script>
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('fileInput');
        const resultDiv = document.getElementById('result');

        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                extractText(files[0]);
            }
        });

        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                extractText(e.target.files[0]);
            }
        });

        function extractText(file) {
            resultDiv.style.display = 'none';
            
            const formData = new FormData();
            formData.append('document_file', file);
            
            resultDiv.innerHTML = '<div class="result"><p>🔍 Extrag text din document...</p></div>';
            resultDiv.style.display = 'block';
            
            fetch('test_ocr_minimal.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Raw response status:', response.status);
                console.log('Raw response headers:', response.headers);
                return response.text();
            })
            .then(text => {
                console.log('Raw response text:', text);
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('JSON parse error:', e);
                    throw new Error('Invalid JSON response: ' + text);
                }
            })
            .then(data => {
                let html = '<div class="result' + (data.success ? '' : ' error') + '">';
                
                if (data.success) {
                    html += `
                        <h3>✅ Extragere Completă</h3>
                        <p><strong>Tip fișier:</strong> ${data.file_type}</p>
                        <p><strong>Lungime text:</strong> ${data.text_length} caractere</p>
                        <p><strong>Metodă extragere:</strong> ${data.extraction_method}</p>
                    `;
                    
                    if (data.extracted_text) {
                        html += `
                            <h4>Text Extras:</h4>
                            <div class="text-preview">${data.extracted_text}</div>
                        `;
                    }
                    
                    if (data.debug) {
                        html += `
                            <div class="debug-info">
                                <strong>Debug Info:</strong>
                                ${JSON.stringify(data.debug, null, 2)}
                            </div>
                        `;
                    }
                } else {
                    html += `
                        <h3>❌ Eroare la Extragere</h3>
                        <p><strong>Eroare:</strong> ${data.error}</p>
                    `;
                    
                    if (data.debug) {
                        html += `<div class="debug-info">${data.debug}</div>`;
                    }
                }
                
                html += '</div>';
                resultDiv.innerHTML = html;
            })
            .catch(error => {
                resultDiv.innerHTML = `
                    <div class="result error">
                        <h3>❌ Eroare de Rețea</h3>
                        <p>Eroare: ${error.message}</p>
                    </div>
                `;
            });
        }

        function testSimple() {
            const resultDiv = document.getElementById('simpleResult');
            resultDiv.innerHTML = '<p>Testez conexiunea...</p>';
            
            const formData = new FormData();
            formData.append('document_file', new File(['test'], 'test.txt', { type: 'text/plain' }));
            
            fetch('test_simple.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Simple test - Raw response status:', response.status);
                return response.text();
            })
            .then(text => {
                console.log('Simple test - Raw response text:', text);
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Simple test - JSON parse error:', e);
                    throw new Error('Invalid JSON response: ' + text);
                }
            })
            .then(data => {
                let html = '<div class="result' + (data.success ? '' : ' error') + '">';
                html += `<h4>Test Conexiune:</h4>`;
                html += `<p><strong>Status:</strong> ${data.success ? 'Succes' : 'Eșec'}</p>`;
                html += `<p><strong>Mesaj:</strong> ${data.message}</p>`;
                html += `<p><strong>Timestamp:</strong> ${data.timestamp}</p>`;
                if (data.file_info) {
                    html += `<p><strong>Info fișier:</strong> ${JSON.stringify(data.file_info)}</p>`;
                }
                html += '</div>';
                resultDiv.innerHTML = html;
            })
            .catch(error => {
                resultDiv.innerHTML = `
                    <div class="result error">
                        <p>Eroare la testarea conexiunii: ${error.message}</p>
                    </div>
                `;
            });
        }

        function testHardcoded() {
            const resultDiv = document.getElementById('hardcodedResult');
            resultDiv.innerHTML = '<p>Testez răspunsul hardcodat...</p>';
                
            const data = {
                success: true,
                file_type: 'png',
                text_length: 150,
                extraction_method: 'OCR (Tesseract)',
                extracted_text: 'Test text extracted from image',
                file_size: 1024,
                file_exists: true,
                has_text: true
            };
            
            console.log('Hardcoded test - Data:', data);
            
            let html = '<div class="result' + (data.success ? '' : ' error') + '">';
            html += `<h4>Test Răspuns Hardcodat:</h4>`;
            html += `<p><strong>Tip fișier:</strong> ${data.file_type}</p>`;
            html += `<p><strong>Lungime text:</strong> ${data.text_length} caractere</p>`;
            html += `<p><strong>Metodă extragere:</strong> ${data.extraction_method}</p>`;
            if (data.extracted_text) {
                html += `<h4>Text Extras:</h4>`;
                html += `<div class="text-preview">${data.extracted_text}</div>`;
            }
            html += '</div>';
            resultDiv.innerHTML = html;
        }

        function testTesseract() {
            const resultDiv = document.getElementById('tesseractResult');
            resultDiv.innerHTML = '<p>Testez Tesseract...</p>';
            
            fetch('test_tesseract_simple.php')
            .then(response => response.json())
            .then(data => {
                let html = '<div class="result' + (data.tesseract_available ? '' : ' error') + '">';
                html += `<h4>Tesseract Status:</h4>`;
                html += `<p><strong>shell_exec disponibil:</strong> ${data.shell_exec_available ? 'Da' : 'Nu'}</p>`;
                html += `<p><strong>Tesseract disponibil:</strong> ${data.tesseract_available ? 'Da' : 'Nu'}</p>`;
                if (data.tesseract_version) {
                    html += `<p><strong>Versiune:</strong> ${data.tesseract_version}</p>`;
                }
                if (data.tesseract_path) {
                    html += `<p><strong>Cale:</strong> ${data.tesseract_path}</p>`;
                }
                if (data.test_command) {
                    html += `<p><strong>Comandă test:</strong> ${data.test_command}</p>`;
                }
                if (data.test_output) {
                    html += `<p><strong>Output test:</strong> ${data.test_output}</p>`;
                }
                if (data.error) {
                    html += `<p><strong>Eroare:</strong> ${data.error}</p>`;
                }
                html += '</div>';
                resultDiv.innerHTML = html;
            })
            .catch(error => {
                resultDiv.innerHTML = `
                    <div class="result error">
                        <p>Eroare la testarea Tesseract: ${error.message}</p>
                    </div>
                `;
            });
        }
    </script>
</body>
</html> 
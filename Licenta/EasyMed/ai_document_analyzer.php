<?php
/**
 * Analizator AI pentru documente medicale
 * 
 * Această clasă oferă funcționalități pentru analiza automată a documentelor medicale,
 * incluzând extragerea textului din diferite formate (PDF, imagini, Word),
 * clasificarea tipului de document și generarea de titluri sugestive.
 * 
 * @author EasyMed System
 * @version 1.0
 * @since 2024
 */
require_once 'db_connection.php';

/**
 * Clasa DocumentAnalyzer - Analizează documente medicale folosind AI
 * 
 * Această clasă implementează algoritmi de analiză pentru documente medicale,
 * incluzând recunoașterea tipurilor de documente, extragerea textului și
 * generarea de titluri descriptive în limba română.
 */
class DocumentAnalyzer {
    
    /**
     * Cuvinte cheie pentru clasificarea documentelor medicale
     * 
     * Array asociativ care conține cuvinte cheie specifice pentru fiecare tip
     * de document medical, folosite pentru clasificarea automată.
     */
    private $documentKeywords = [
        'analize' => [
            'hemoglobina', 'glicemie', 'colesterol', 'leucocite', 'hematocrit', 
            'trombocite', 'eritrocite', 'uree', 'creatinina', 'bilirubina',
            'transaminaze', 'fosfataza', 'amilaza', 'lipaza', 'proteine',
            'albumin', 'globuline', 'fibrinogen', 'coagulare', 'sedimentare'
        ],
        'imagistica_medicala' => [
            // Cuvinte cheie pentru radiografii
            'radiografie', 'x-ray', 'fractura', 'pulmonar', 'toracic',
            'coloana', 'craniu', 'membru', 'articulatie', 'pneumonie',
            'tuberculoza', 'nodul', 'masa', 'calcificare', 'osteoporoza',
            // Cuvinte cheie pentru ecografii
            'ecografie', 'ultrasunet', 'fetus', 'cardiac', 'abdominal',
            'tiroida', 'mamara', 'prostata', 'vesica', 'rinichi',
            'ficat', 'splina', 'pancreas', 'vase', 'doppler'
        ],
        'observatie' => [
            'fisa', 'fisa de observatie', 'foaie', 'observatie', 'internare', 'spital', 'sectie', 'pat', 'camera', 
            'evolutie', 'simptome', 'examen', 'examinare', 'monitorizare', 'vital'
        ],
        'scrisori' => [
            'scrisoare medicala', 'scrisoare medicală', 'scrisoare', 'aviz', 'consultatie', 'recomandare',
            'certificat', 'adeverinta', 'concluzie', 'diagnostic', 'prognostic',
            'tratament', 'control', 'urmarire', 'specialist', 'consilium'
        ],
        'externari' => [
            'externare', 'externar', 'bilet', 'de externare', 'spital',
            'discharge', 'sumar', 'sumar medical', 'concluzie', 'recomandari',
            'medicamente', 'tratament', 'urmarire', 'control', 'data externare'
        ]
    ];
    
    /**
     * Termeni medicali pentru clasificarea documentelor
     * 
     * Array asociativ cu termeni medicali specifici pentru fiecare categorie
     * de documente, folosiți pentru îmbunătățirea preciziei clasificării.
     */
    private $medicalTerms = [
        'Analize' => ['sanguine', 'urina', 'biochimie', 'hematologie'],
        'Imagistica Medicala' => [
            // Termeni pentru radiografii
            'radiografie', 'toracica', 'columna', 'membru', 'craniu',
            // Termeni pentru ecografii
            'ecografie', 'abdominala', 'cardiac', 'tiroida', 'mamara', 'prostata', 'ficat', 'splina', 'pancreas', 'vase', 'doppler'
        ],
        'Observatie' => ['internare', 'spital', 'evolutie', 'monitorizare'],
        'Scrisoare' => ['medicala', 'aviz', 'recomandare'],
        'Externare' => ['externare', 'sumar', 'concluzie', 'discharge']
    ];
    
    /**
     * Analizează documentul încărcat și sugerează un titlu
     * 
     * Această metodă este punctul principal de intrare pentru analiza documentelor.
     * Extrage textul din document, clasifică tipul și generează un titlu sugestiv.
     * 
     * @param string $filePath Calea către fișierul documentului
     * @param string $originalFileName Numele original al fișierului
     * @return array Array cu rezultatele analizei (success, suggested_title, document_type, confidence, debug)
     */
    public function analyzeDocument($filePath, $originalFileName) {
        try {
            // Extrage textul din document
            $text = $this->extractTextFromDocument($filePath, $originalFileName);
            
            // Curăță textul pentru procesare JSON
            $text = $this->cleanTextForJSON($text);
            
            // Text pentru debugging (primele 500 de caractere)
            $debugText = substr($text, 0, 500);
            
            // Clasifică tipul documentului
            $documentType = $this->classifyDocumentType($filePath, $text, $originalFileName);
            
            // Generează titlul sugestiv
            $suggestedTitle = $this->generateTitle($text, $documentType, $originalFileName);
            
            // Gestionare caz special când nu s-a extras text
            if (empty($text)) {
                $extension = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));
                switch ($extension) {
                    case 'pdf':
                        $suggestedTitle = 'Document PDF Medical';
                        break;
                    case 'jpg':
                    case 'jpeg':
                    case 'png':
                        $suggestedTitle = 'Imagine Medicala';
                        break;
                    case 'doc':
                    case 'docx':
                        $suggestedTitle = 'Document Word Medical';
                        break;
                    default:
                        $suggestedTitle = 'Document Medical';
                }
                error_log("[AI] Nu s-a extras text, folosind titlu bazat pe tipul fișierului: $suggestedTitle");
            } else {
                if ($documentType === 'scrisori') {
                    $suggestedTitle = 'Scrisoare Medicala';
                    error_log("[AI] Text extras, folosind titlu specific pentru scrisori: $suggestedTitle");
                }
            }
            
            // Returnează rezultatele analizei
            return [
                'success' => true,
                'suggested_title' => $suggestedTitle,
                'document_type' => $documentType,
                'confidence' => $this->calculateConfidence($text, $documentType),
                'debug' => [
                    'extracted_text_length' => strlen($text),
                    'extracted_text_preview' => $debugText,
                    'document_type_score' => $this->getDocumentTypeScores($text),
                    'raw_text_sample' => substr($text, 0, 200), // Primele 200 de caractere
                    'title_matches' => $this->findTitleMatches($text),
                    'ocr_failed' => empty($text),
                    'file_extension' => strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION))
                ]
            ];
            
        } catch (Exception $e) {
            // Returnează eroare în caz de excepție
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'suggested_title' => $this->generateFallbackTitle($originalFileName),
                'document_type' => 'alte',
                'debug' => [
                    'error_details' => $e->getTraceAsString()
                ]
            ];
        }
    }
    
    /**
     * Extrage text din diferite tipuri de documente
     * 
     * Această metodă determină tipul fișierului și apelează metoda corespunzătoare
     * pentru extragerea textului (PDF, imagine, Word).
     * 
     * @param string $filePath Calea către fișier
     * @param string $originalFileName Numele original al fișierului
     * @return string Textul extras din document
     */
    private function extractTextFromDocument($filePath, $originalFileName = null) {
        error_log("extractTextFromDocument: început cu $filePath, original: $originalFileName");

        // Verifică dacă fișierul există
        if (!file_exists($filePath)) {
            error_log("extractTextFromDocument: fișierul nu există: $filePath");
            return '';
        }
        error_log("extractTextFromDocument: fișierul există");

        // Determină extensia fișierului
        if ($originalFileName) {
            $extension = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));
            error_log("extractTextFromDocument: folosind extensia din numele original: '$extension'");
        } else {
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            error_log("extractTextFromDocument: folosind extensia din calea fișierului: '$extension'");
        }
        
        // Apelează metoda corespunzătoare în funcție de tipul fișierului
        switch ($extension) {
            case 'pdf':
                error_log("extractTextFromDocument: apelând extractTextFromPDF");
                return $this->extractTextFromPDF($filePath);
            case 'jpg':
            case 'jpeg':
            case 'png':
                error_log("extractTextFromDocument: apelând extractTextFromImage");
                return $this->extractTextFromImage($filePath);
            case 'doc':
            case 'docx':
                error_log("extractTextFromDocument: apelând extractTextFromWord");
                return $this->extractTextFromWord($filePath);
            default:
                error_log("extractTextFromDocument: tip de fișier nesuportat '$extension', returnează string gol");
                return '';
        }
        error_log("extractTextFromDocument: sfârșit");
    }
    
    /**
     * Extrage text din PDF folosind pdftotext (necesită poppler-utils)
     * 
     * Această metodă folosește utilitarul pdftotext pentru extragerea textului
     * din fișiere PDF, cu suport pentru layout-ul original.
     * 
     * @param string $filePath Calea către fișierul PDF
     * @return string Textul extras din PDF
     */
    private function extractTextFromPDF($filePath) {
        error_log("extractTextFromPDF: început cu $filePath");
        if (function_exists('shell_exec')) {
            error_log("extractTextFromPDF: funcția shell_exec există");
            $pdftotextPaths = [
                'pdftotext',
                'C:\\poppler\\poppler-24.08.0\\bin\\pdftotext.exe',
                'C:\\poppler\\poppler-24.08.0\\Library\\bin\\pdftotext.exe'
            ];
            
            // Încearcă diferite căi pentru pdftotext
            foreach ($pdftotextPaths as $pdftotext) {
                error_log("extractTextFromPDF: încercând calea pdftotext: $pdftotext");
                
                // Încearcă cu layout-ul păstrat
                $cmd = "\"$pdftotext\" -q -layout \"$filePath\" -";
                error_log("extractTextFromPDF: rulând comanda: $cmd");
                $output = shell_exec($cmd);
                error_log("extractTextFromPDF: shell_exec completat cu layout, lungimea output: " . ($output ? strlen($output) : 'null'));
                if ($output) {
                    error_log("extractTextFromPDF: extragere reușită cu layout");
                    return trim($output);
                }
                
                // Încearcă fără layout
                $cmd = "\"$pdftotext\" -q \"$filePath\" -";
                error_log("extractTextFromPDF: rulând comanda: $cmd");
                $output = shell_exec($cmd);
                error_log("extractTextFromPDF: shell_exec completat fără layout, lungimea output: " . ($output ? strlen($output) : 'null'));
                if ($output) {
                    error_log("extractTextFromPDF: extragere reușită fără layout");
                    return trim($output);
                }
            }
        } else {
            error_log("extractTextFromPDF: funcția shell_exec nu există");
        }
        
        // Fallback: încearcă să citească fișierul ca text
        error_log("extractTextFromPDF: încercând file_get_contents ca fallback");
        return file_get_contents($filePath);
    }
    
    /**
     * Extrage text din imagine folosind OCR (necesită tesseract)
     * 
     * Această metodă folosește Tesseract OCR pentru recunoașterea textului
     * din imagini, cu suport pentru limba română și engleză.
     * 
     * @param string $filePath Calea către fișierul imagine
     * @return string Textul extras din imagine
     */
    private function extractTextFromImage($filePath) {
        error_log("extractTextFromImage: început cu $filePath");
        error_log('Calea fișierului Tesseract: ' . $filePath);
        error_log('Fișierul există: ' . (file_exists($filePath) ? 'da' : 'nu'));
        error_log('Dimensiunea fișierului: ' . (file_exists($filePath) ? filesize($filePath) : 'N/A'));

        if (function_exists('shell_exec')) {
            error_log("extractTextFromImage: funcția shell_exec există");
            
            // Testează versiunea Tesseract
            $testCmd = 'tesseract --version 2>&1';
            $testOutput = shell_exec($testCmd);
            error_log("extractTextFromImage: test versiune Tesseract: " . ($testOutput ? $testOutput : 'Tesseract nu a fost găsit în PATH'));
            
            $tesseractPaths = [
                'tesseract',
                'C:\\Program Files\\Tesseract-OCR\\tesseract.exe',
                'C:\\Program Files (x86)\\Tesseract-OCR\\tesseract.exe'
            ];
            
            // Încearcă diferite căi pentru Tesseract
            foreach ($tesseractPaths as $tesseract) {
                error_log("extractTextFromImage: încercând calea tesseract: $tesseract");
                $psmModes = [6, 3, 8, 13]; // Diferite moduri de segmentare a paginii
                
                // Încearcă diferite moduri PSM
                foreach ($psmModes as $psm) {
                    error_log("extractTextFromImage: încercând modul PSM: $psm");
                    $cmd = "\"$tesseract\" \"$filePath\" stdout -l ron+eng --psm $psm";
                    error_log("extractTextFromImage: rulând comanda: $cmd");
                    $output = shell_exec($cmd);
                    error_log("extractTextFromImage: shell_exec completat pentru PSM $psm, lungimea output: " . ($output ? strlen($output) : 'null'));
                    if ($output && strlen(trim($output)) > 10) {
                        error_log("extractTextFromImage: extragere reușită cu PSM $psm");
                        return trim($output);
                    }
                }
                
                // Încearcă fără modul PSM
                error_log("extractTextFromImage: încercând fără modul PSM");
                $cmd = "\"$tesseract\" \"$filePath\" stdout -l ron+eng";
                error_log("extractTextFromImage: rulând comanda: $cmd");
                $output = shell_exec($cmd);
                error_log("extractTextFromImage: shell_exec completat fără PSM, lungimea output: " . ($output ? strlen($output) : 'null'));
                if ($output && strlen(trim($output)) > 10) {
                    error_log("extractTextFromImage: extragere reușită fără PSM");
                    return trim($output);
                }
            }
        }
        
        // Returnează string gol dacă OCR-ul a eșuat
        error_log("extractTextFromImage: OCR eșuat, returnează string gol");
        return '';
    }
    
    /**
     * Extrage text din documente Word
     * 
     * Această metodă extrage textul din fișiere DOCX folosind PHPZip.
     * 
     * @param string $filePath Calea către fișierul DOCX
     * @return string Textul extras din DOCX
     */
    private function extractTextFromWord($filePath) {
        error_log("extractTextFromWord: început cu $filePath");
        if (pathinfo($filePath, PATHINFO_EXTENSION) === 'docx') {
            error_log("extractTextFromWord: procesând fișier DOCX");
            $zip = new ZipArchive;
            if ($zip->open($filePath) === TRUE) {
                error_log("extractTextFromWord: ZIP arhivă deschisă cu succes");
                $text = $zip->getFromName('word/document.xml');
                $zip->close();
                
                $text = strip_tags($text);
                $text = preg_replace('/\s+/', ' ', $text);
                error_log("extractTextFromWord: lungimea textului extras: " . strlen($text));
                return trim($text);
            } else {
                error_log("extractTextFromWord: nu s-a putut deschide arhiva ZIP");
            }
        } else {
            error_log("extractTextFromWord: nu este un fișier DOCX");
        }
        
        error_log("extractTextFromWord: returnează string gol");
        return '';
    }
    
    /**
     * Clasifică tipul documentului pe baza conținutului
     * 
     * Această metodă folosește cuvinte cheie și termeni medicali pentru a
     * determina tipul documentului.
     * 
     * @param string $filePath Calea către fișierul documentului
     * @param string $text Textul extras din document
     * @param string $originalFileName Numele original al fișierului
     * @return string Tipul documentului (analize, imagistica_medicala, observatie, scrisori, externari)
     */
    private function classifyDocumentType($filePath, $text, $originalFileName) {
    
        if (isset($filePath) && $this->isMostlyDarkImage($filePath, $originalFileName)) {
            error_log("[AI] isMostlyDarkImage rezultat: DA (forțează imagistica_medicala)");
            return 'imagistica_medicala';
        }

        $textNorm = $this->normalize($text);
        error_log("[AI] Text normalizat pentru căutarea de fraze: $textNorm");
        error_log("[AI] Lungimea textului original: " . strlen($text));
        error_log("[AI] Lungimea textului normalizat: " . strlen($textNorm));
        error_log("[AI] Antetul textului (primele 200 caractere): " . substr($textNorm, 0, 200));
        error_log("[AI] Contine 'scrisoare': " . (strpos($textNorm, 'scrisoare') !== false ? 'DA' : 'NU'));
        error_log("[AI] Contine 'medicala': " . (strpos($textNorm, 'medicala') !== false ? 'DA' : 'NU'));
        error_log("[AI] Contine 'scrisoare medicala': " . (strpos($textNorm, 'scrisoare medicala') !== false ? 'DA' : 'NU'));
        if (strpos($textNorm, 'bilet de externare') !== false) {
            return 'externari';
        }
        if (strpos($textNorm, 'scrisoare medicala') !== false || 
            strpos($textNorm, 'scrisoare medicală') !== false ||
            strpos($textNorm, 'scrisoare') !== false) {
            error_log("[AI] S-a găsit potrivire pentru scrisoare medicala, returnând 'scrisori'");
            return 'scrisori';
        }
      
        if (
            strpos($textNorm, 'foaie de observatie clinica generala') !== false ||
            strpos($textNorm, 'foaie de observatie') !== false ||
            strpos($textNorm, 'foaie de observaie') !== false ||
            strpos($textNorm, 'foaie de observa') !== false ||
            strpos($textNorm, 'observatie clinica') !== false ||
            strpos($textNorm, 'observaie clinic') !== false ||
            strpos($textNorm, 'observatie') !== false ||
            strpos($textNorm, 'observaie') !== false
        ) {
            return 'observatie';
        }
        if (strpos($textNorm, 'buletin de analize') !== false || strpos($textNorm, 'analize medicale') !== false) {
            return 'analize';
        }
        if (strpos($textNorm, 'radiografie') !== false || strpos($textNorm, 'ecografie') !== false) {
            return 'imagistica_medicala';
        }

        $title = $this->extractDocumentTitle($text);
        $titleNorm = $this->normalize($title);
        error_log("[AI] Titlul documentului extras: $titleNorm");
        if (strpos($titleNorm, 'foaie de observatie clinica generala') !== false ||
            strpos($titleNorm, 'foaie de observatie') !== false) {
            return 'observatie';
        }
        if (strpos($titleNorm, 'scrisoare medicala') !== false) {
            return 'scrisori';
        }
        if (strpos($titleNorm, 'bilet de externare') !== false) {
            return 'externari';
        }
        if (strpos($titleNorm, 'analize') !== false) {
            return 'analize';
        }
        if (strpos($titleNorm, 'radiografie') !== false || strpos($titleNorm, 'ecografie') !== false) {
            return 'imagistica_medicala';
        }

        $scores = [];
        foreach ($this->documentKeywords as $type => $keywords) {
            $score = 0;
            foreach ($keywords as $keyword) {
                $keywordNorm = $this->normalize($keyword);
                if (strpos($textNorm, $keywordNorm) !== false) {
                    $score += 2;
                    error_log("[AI] S-a găsit potrivire pentru cuvântul cheie: '$keywordNorm' pentru tipul '$type'");
                }
                $words = explode(' ', $keywordNorm);
                foreach ($words as $word) {
                    if (strlen($word) > 3 && strpos($textNorm, $word) !== false) {
                        $score += 1;
                        error_log("[AI] S-a găsit potrivire pentru cuvântul: '$word' pentru tipul '$type'");
                    }
                }
            }
            $scores[$type] = $score;
            error_log("[AI] Scor pentru tipul '$type': $score");
        }
        
        error_log("[AI] Toate scorurile: " . print_r($scores, true));
        $maxScore = max($scores);
        if ($maxScore > 0) {
            $bestType = array_search($maxScore, $scores);
            error_log("[AI] Cel mai bun tip găsit: '$bestType' cu scorul $maxScore");
            return $bestType;
        }
        
        error_log("[AI] Nu s-au găsit potriviri, returnând 'alte'");
        return 'alte';
    }
    
    /**
     * Generează titlul pe baza conținutului și tipului
     * 
     * Această metodă generează un titlu sugestiv în funcție de tipul documentului.
     * 
     * @param string $text Textul extras din document
     * @param string $documentType Tipul documentului
     * @param string $originalFileName Numele original al fișierului
     * @return string Titlul sugestiv
     */
    private function generateTitle($text, $documentType, $originalFileName) {
        switch ($documentType) {
            case 'analize':
                return 'Buletin de analize medicale';
            case 'imagistica_medicala':
                return 'Imagistica medicala';
            case 'observatie':
                return 'Foaie de observatie clinica generala';
            case 'scrisori':
                return 'Scrisoare medicala';
            case 'externari':
                return 'Bilet de externare';
            default:
                return $this->generateFallbackTitle($originalFileName);
        }
    }
    
    /**
     * Extrage informații cheie din textul documentului
     * 
     * Această metodă caută date, nume de pacienți și nume de doctori în text.
     * 
     * @param string $text Textul extras din document
     * @return array Informații cheie (date, nume pacient, nume doctor, termeni medicali)
     */
    private function extractKeyInformation($text) {
        $info = [];
        
        preg_match_all('/(\d{1,2}[\.\/]\d{1,2}[\.\/]\d{2,4})/', $text, $dates);
        if (!empty($dates[0])) {
            $info['date'] = $dates[0][0];
        }
        
        preg_match_all('/(?:pacient|pacienta|nume|prenume)[\s:]+([A-Z][a-z]+(?:\s+[A-Z][a-z]+)*)/', $text, $names);
        if (!empty($names[1])) {
            $info['patient_name'] = $names[1][0];
        }
        
        preg_match_all('/(?:dr\.|doctor|medic)[\s\.]+([A-Z][a-z]+(?:\s+[A-Z][a-z]+)*)/', $text, $doctors);
        if (!empty($doctors[1])) {
            $info['doctor_name'] = $doctors[1][0];
        }
        
        foreach ($this->medicalTerms as $category => $terms) {
            foreach ($terms as $term) {
                if (stripos($text, $term) !== false) {
                    $info['medical_terms'][] = $term;
                }
            }
        }
        
        return $info;
    }
    
    /**
     * Generează titlu pentru documente de analiză
     * 
     * Această metodă generează un titlu pentru documente care conțin analize.
     * 
     * @param array $keyInfo Informații cheie extrase
     * @return string Titlul documentului de analiză
     */
    private function generateAnalysisTitle($keyInfo) {
        $title = 'Analize';
        
        if (!empty($keyInfo['medical_terms'])) {
            $title .= ' ' . ucfirst($keyInfo['medical_terms'][0]);
        } else {
            $title .= ' Medicale';
        }
        
        if (!empty($keyInfo['date'])) {
            $title .= ' - ' . $keyInfo['date'];
        }
        
        return $title;
    }
    
    /**
     * Generează titlu pentru documente de imagistica_medicala
     * 
     * Această metodă generează un titlu pentru documente care conțin imagini.
     * 
     * @param array $keyInfo Informații cheie extrase
     * @param string $text Textul extras din document (opțional)
     * @return string Titlul documentului de imagistica_medicala
     */
    private function generateImagingTitle($keyInfo, $text = '') {

        $textNorm = $this->normalize($text);
        $isXray = (strpos($textNorm, 'radiografie') !== false || strpos($textNorm, 'x-ray') !== false);
        $isEcho = (strpos($textNorm, 'ecografie') !== false || strpos($textNorm, 'ultrasunet') !== false);
        $title = 'Imagistica Medicala';
        if ($isXray) {
            $title .= ' - Radiografie';
        } elseif ($isEcho) {
            $title .= ' - Ecografie';
        }
        if (!empty($keyInfo['medical_terms'])) {
            $title .= ' ' . ucfirst($keyInfo['medical_terms'][0]);
        }
        if (!empty($keyInfo['date'])) {
            $title .= ' - ' . $keyInfo['date'];
        }
        return $title;
    }
    
    /**
     * Generează titlu pentru documente de observatie
     * 
     * Această metodă generează un titlu pentru documente care conțin observații.
     * 
     * @param array $keyInfo Informații cheie extrase
     * @param string $text Textul extras din document (opțional)
     * @return string Titlul documentului de observatie
     */
    private function generateObservationTitle($keyInfo, $text = '') {
        $textNorm = $this->normalize($text);
        if (strpos($textNorm, 'foaie de observatie clinica generala') !== false) {
            return 'Fișă de observație clinică generală';
        }
        if (strpos($textNorm, 'foaie de observatie') !== false) {
            return 'Fișă de observație';
        }
        $title = 'Fișă de Observație';
        if (!empty($keyInfo['medical_terms'])) {
            $title .= ' - ' . ucfirst($keyInfo['medical_terms'][0]);
        }
        if (!empty($keyInfo['date'])) {
            $title .= ' - ' . $keyInfo['date'];
        }
        return $title;
    }
    
    /**
     * Generează titlu pentru scrisori medicale
     * 
     * Această metodă generează un titlu pentru scrisori medicale.
     * 
     * @param array $keyInfo Informații cheie extrase
     * @return string Titlul scrisorii medicale
     */
    private function generateLetterTitle($keyInfo) {
        $title = 'Scrisoare Medicală';
        
        if (!empty($keyInfo['doctor_name'])) {
            $title .= ' - Dr. ' . $keyInfo['doctor_name'];
        }
        
        if (!empty($keyInfo['date'])) {
            $title .= ' - ' . $keyInfo['date'];
        }
        
        return $title;
    }
    
    /**
     * Generează titlu pentru documente de externare
     * 
     * Această metodă generează un titlu pentru documente care conțin externare.
     * 
     * @param array $keyInfo Informații cheie extrase
     * @return string Titlul documentului de externare
     */
    private function generateDischargeTitle($keyInfo) {
        $title = 'Bilet de Externare';
        
        if (!empty($keyInfo['medical_terms'])) {
            $title .= ' - ' . ucfirst($keyInfo['medical_terms'][0]);
        }
        
        if (!empty($keyInfo['date'])) {
            $title .= ' - ' . $keyInfo['date'];
        }
        
        return $title;
    }
    
    /**
     * Generează titlu de fallback din numele fișierului
     * 
     * Această metodă generează un titlu de fallback dacă nu se poate extrage un titlu
     * sugestiv din text.
     * 
     * @param string $originalFileName Numele original al fișierului
     * @return string Titlul de fallback
     */
    private function generateFallbackTitle($originalFileName) {
        $extension = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));
        $name = pathinfo($originalFileName, PATHINFO_FILENAME);
        
        $name = str_replace(['_', '-'], ' ', $name);
        $name = preg_replace('/[0-9]+/', '', $name);
        $name = trim($name);
        $name = ucwords($name);
        
        if (empty($name) || strlen($name) < 3) {
            switch ($extension) {
                case 'pdf':
                    return 'Document PDF';
                case 'jpg':
                case 'jpeg':
                case 'png':
                    return 'Imagine Medicala';
                case 'doc':
                case 'docx':
                    return 'Document Word';
                default:
                    return 'Document Medical';
            }
        }
        
        return $name;
    }
    
    /**
     * Obține scorurile pentru tipul documentului pentru debugging
     * 
     * Această metodă calculează scorurile pentru fiecare tip de document
     * bazat pe cuvinte cheie și cuvintele din text.
     * 
     * @param string $text Textul extras din document
     * @return array Scorurile pentru fiecare tip de document
     */
    private function getDocumentTypeScores($text) {
        $textNorm = $this->normalize($text);
        $scores = [];
        foreach ($this->documentKeywords as $type => $keywords) {
            $score = 0;
            $matchedKeywords = [];
            foreach ($keywords as $keyword) {
                $keywordNorm = $this->normalize($keyword);
                if (strpos($textNorm, $keywordNorm) !== false) {
                    $score += 2;
                    $matchedKeywords[] = $keyword;
                }
                $words = explode(' ', $keywordNorm);
                foreach ($words as $word) {
                    if (strlen($word) > 3 && strpos($textNorm, $word) !== false) {
                        $score += 1;
                        if (!in_array($word, $matchedKeywords)) {
                            $matchedKeywords[] = $word;
                        }
                    }
                }
            }
            $scores[$type] = [
                'score' => $score,
                'matched_keywords' => $matchedKeywords
            ];
        }
        return $scores;
    }

    /**
     * Găsește potrivirile pentru titlu pentru debugging
     * 
     * Această metodă caută potriviri pentru titluri în text.
     * 
     * @param string $text Textul extras din document
     * @return array Potrivirile găsite
     */
    private function findTitleMatches($text) {
        $textNorm = $this->normalize($text);
        $matches = [];
        $patterns = [
            '/foaie\s+de\s+observatie/',
            '/foaie\s+de\s+observatie\s+clinica\s+generala/',
            '/foaie\s+de\s+observatie\s+clinica/'
        ];
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $textNorm, $found)) {
                $matches[] = $found[0];
            }
        }
        return $matches;
    }

    /**
     * Calculează scorul de încredere pentru analiza
     * 
     * Această metodă calculează scorul de încredere pentru un document
     * bazat pe cuvinte cheie și scorurile pentru fiecare tip de document.
     * 
     * @param string $text Textul extras din document
     * @param string $documentType Tipul documentului
     * @return float Scorul de încredere
     */
    private function calculateConfidence($text, $documentType) {
        if (empty($text)) {
            return 0.1;
        }
        $textNorm = $this->normalize($text);
        $keywords = $this->documentKeywords[$documentType] ?? [];
        $totalMatches = 0;
        foreach ($keywords as $keyword) {
            $keywordNorm = $this->normalize($keyword);
            if (strpos($textNorm, $keywordNorm) !== false) {
                $totalMatches += 2;
            }
            $words = explode(' ', $keywordNorm);
            foreach ($words as $word) {
                if (strlen($word) > 3 && strpos($textNorm, $word) !== false) {
                    $totalMatches += 1;
                }
            }
        }
        if ($documentType === 'observatie' && 
            (preg_match('/foaie\s+de\s+observatie/', $textNorm) || 
             preg_match('/foaie\s+de\s+observatie\s+clinica\s+generala/', $textNorm) ||
             preg_match('/foaie\s+de\s+observatie\s+clinica/', $textNorm))) {
            $totalMatches += 20;
        }
        $baseConfidence = min(1.0, $totalMatches / max(1, count($keywords) * 0.2));
        if (strlen($textNorm) > 100) {
            $baseConfidence = min(1.0, $baseConfidence * 1.2);
        }
        return round($baseConfidence, 2);
    }

    private function normalize($string) {
        $string = strtolower($string);
        $string = str_replace(
            ['ă', 'â', 'î', 'ș', 'ş', 'ț', 'ţ', 'ă', 'â', 'î', 'ș', 'ş', 'ț', 'ţ'],
            ['a', 'a', 'i', 's', 's', 't', 't', 'a', 'a', 'i', 's', 's', 't', 't'],
            $string
        );
        return $string;
    }
    
    public function cleanTextForJSON($text) {
        if (empty($text)) {
            return '';
        }
        
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);
        
        if (!mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding($text, 'UTF-8', 'ISO-8859-1');
        }
        
        $text = preg_replace('/[\x80-\x9F]/', '', $text);
        
        $text = preg_replace('/[^\x20-\x7E\xA0-\xFF]/', '', $text);
        
        return $text;
    }

    private function isMostlyDarkImage($filePath, $originalFileName = null) {
        error_log("[AI] isMostlyDarkImage: apelat pentru $filePath");
        if (!function_exists('imagecreatefromjpeg') && !function_exists('imagecreatefrompng')) {
            error_log("[AI] isMostlyDarkImage: GD nu este disponibil");
            return false;
        }
        $ext = $originalFileName ? strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION)) : strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if ($ext === 'jpg' || $ext === 'jpeg') {
            $img = @imagecreatefromjpeg($filePath);
        } elseif ($ext === 'png') {
            $img = @imagecreatefrompng($filePath);
        } else {
            error_log("[AI] isMostlyDarkImage: Nu este un tip de imagine suportat ($ext)");
            return false;
        }
        if (!$img) {
            error_log("[AI] isMostlyDarkImage: imagecreatefrom... a eșuat pentru $filePath");
            return false;
        }
        $w = imagesx($img);
        $h = imagesy($img);
        $sample = 0;
        $dark = 0;
        for ($x = 0; $x < $w; $x += 10) {
            for ($y = 0; $y < $h; $y += 10) {
                $rgb = imagecolorat($img, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                $brightness = ($r + $g + $b) / 3;
                if ($brightness < 180) $dark++;
                $sample++;
            }
        }
        imagedestroy($img);
        $ratio = ($sample > 0) ? ($dark / $sample) : 0;
        error_log("[AI] isMostlyDarkImage: pixeli închis = $dark, total eșantioane = $sample, raport = $ratio");
        return ($sample > 0 && $ratio > 0.1);
    }

    private function extractDocumentTitle($text) {
        $lines = preg_split('/\\r?\\n/', $text);
        foreach ($lines as $line) {
            $line = trim($line);
            if (strlen($line) > 15 && strtoupper($line) === $line && preg_match('/[A-Z]/', $line) && preg_match('/[A-Z]{3,}/', $line)) {
                return $line;
            }
        }
        return '';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document_file'])) {
    
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        exit(0);
    }
    
    error_log("AI Analyzer API: Cerere primită la " . date('Y-m-d H:i:s'));
    error_log("AI Analyzer API: Metoda: " . $_SERVER['REQUEST_METHOD']);
    error_log("AI Analyzer API: Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'nu este setat'));
    error_log("AI Analyzer API: Array-ul Files: " . print_r($_FILES, true));
    
    try {
        $analyzer = new DocumentAnalyzer();
        $file = $_FILES['document_file'];
        
        if ($file['error'] === UPLOAD_ERR_OK) {
            error_log("AI Analyzer API: Încărcare fișier OK");
            $tempPath = $file['tmp_name'];
            $originalName = $file['name'];
            
            error_log("AI Analyzer API: Calea temporară: $tempPath");
            error_log("AI Analyzer API: Nume original: $originalName");
            
            if (!file_exists($tempPath)) {
                throw new Exception("Fișierul încărcat nu a fost găsit la: $tempPath");
            }
            
            error_log("AI Analyzer API: Începe analiza...");
            error_log("AI Analyzer API: Calea temporară: $tempPath, Nume original: $originalName");
            $result = $analyzer->analyzeDocument($tempPath, $originalName);
            error_log("AI Analyzer API: Analiză finalizată, rezultat: " . print_r($result, true));
            
            if (isset($result['suggested_title'])) {
                $result['suggested_title'] = $analyzer->cleanTextForJSON($result['suggested_title']);
            }
            
            if (isset($result['debug']['extracted_text_preview'])) {
                $result['debug']['extracted_text_preview'] = $analyzer->cleanTextForJSON($result['debug']['extracted_text_preview']);
            }
            
            if (isset($result['debug']['raw_text_sample'])) {
                $result['debug']['raw_text_sample'] = $analyzer->cleanTextForJSON($result['debug']['raw_text_sample']);
            }
            
            $jsonResponse = json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($jsonResponse === false) {
                error_log("AI Analyzer API: Codificare JSON a eșuat: " . json_last_error_msg());
                
                $simpleResult = [
                    'success' => $result['success'] ?? false,
                    'suggested_title' => $result['suggested_title'] ?? 'Document Medical',
                    'document_type' => $result['document_type'] ?? 'alte',
                    'confidence' => $result['confidence'] ?? 0.5,
                    'error' => 'Codificare JSON a eșuat: ' . json_last_error_msg()
                ];
                
                $jsonResponse = json_encode($simpleResult);
                if ($jsonResponse === false) {
                    $jsonResponse = json_encode([
                        'success' => false,
                        'error' => 'Codificare JSON a eșuat complet',
                        'suggested_title' => 'Document Medical',
                        'document_type' => 'alte'
                    ]);
                }
            }
            
            error_log("AI Analyzer API: Lungimea răspunsului JSON: " . strlen($jsonResponse));
            error_log("AI Analyzer API: Antetul răspunsului JSON: " . substr($jsonResponse, 0, 200));
            echo $jsonResponse;
            
        } else {
            error_log("AI Analyzer API: Eroare încărcare fișier: " . $file['error']);
            $errorResponse = json_encode([
                'success' => false,
                'error' => 'Eroare încărcare fișier: ' . $file['error'],
                'suggested_title' => 'Document Medical',
                'document_type' => 'alte'
            ]);
            error_log("AI Analyzer API: Răspuns JSON cu eroare: $errorResponse");
            echo $errorResponse;
        }
        
    } catch (Exception $e) {
        error_log("AI Analyzer API: A fost capturată o excepție: " . $e->getMessage());
        $errorResponse = json_encode([
            'success' => false,
            'error' => 'Analiză a eșuat: ' . $e->getMessage(),
            'suggested_title' => 'Document Medical',
            'document_type' => 'alte'
        ]);
        echo $errorResponse;
    }
}
?> 

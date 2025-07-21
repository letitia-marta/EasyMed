package com.example.easymed_mobile;

import android.content.Context;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Button;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;
import java.text.SimpleDateFormat;
import java.util.List;
import java.util.Locale;

/**
 * Adapter pentru afișarea listei de documente medicale în RecyclerView
 * 
 * Această clasă gestionează afișarea documentelor medicale într-o listă
 * interactivă. Fiecare element din listă afișează informațiile despre un
 * document și oferă butoane pentru descărcare și ștergere.
 * 
 * <p>Adapter-ul implementează pattern-ul ViewHolder pentru optimizarea
 * performanței și folosește un listener pentru gestionarea acțiunilor
 * utilizatorului.</p>
 * 
 * @author EasyMed Mobile Team
 * @version 1.0
 * @since 2024
 */
public class DocumentAdapter extends RecyclerView.Adapter<DocumentAdapter.DocumentViewHolder> {
    
    /**
     * Lista de documente de afișat
     */
    private List<Document> documents;
    
    /**
     * Contextul aplicației pentru accesarea resurselor
     */
    private Context context;
    
    /**
     * Listener pentru gestionarea acțiunilor pe documente
     */
    private OnDocumentActionListener listener;

    /**
     * Interfață pentru gestionarea acțiunilor pe documente
     * 
     * Această interfață definește metodele care trebuie implementate
     * de activitatea care folosește adapter-ul pentru a gestiona
     * acțiunile utilizatorului.
     */
    public interface OnDocumentActionListener {
        /**
         * Metodă apelată când utilizatorul dorește să descarce un document
         * 
         * @param document Documentul de descărcat
         */
        void onDownloadDocument(Document document);
        
        /**
         * Metodă apelată când utilizatorul dorește să șteargă un document
         * 
         * @param document Documentul de șters
         */
        void onDeleteDocument(Document document);
    }

    /**
     * Constructor pentru crearea adapter-ului
     * 
     * @param context Contextul aplicației
     * @param documents Lista de documente de afișat
     * @param listener Listener-ul pentru gestionarea acțiunilor
     */
    public DocumentAdapter(Context context, List<Document> documents, OnDocumentActionListener listener) {
        this.context = context;
        this.documents = documents;
        this.listener = listener;
    }

    /**
     * Creează un nou ViewHolder pentru un element din listă
     * 
     * Această metodă este apelată de RecyclerView când este nevoie
     * de un nou ViewHolder pentru afișarea unui element.
     * 
     * @param parent ViewGroup-ul părinte
     * @param viewType Tipul de view (nu este folosit în acest adapter)
     * @return Un nou DocumentViewHolder
     */
    @NonNull
    @Override
    public DocumentViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(context).inflate(R.layout.item_document, parent, false);
        return new DocumentViewHolder(view);
    }

    /**
     * Leagă datele unui document de ViewHolder-ul corespunzător
     * 
     * Această metodă este apelată de RecyclerView pentru a popula
     * un ViewHolder cu datele documentului de la poziția specificată.
     * 
     * @param holder ViewHolder-ul de populat
     * @param position Poziția documentului în listă
     */
    @Override
    public void onBindViewHolder(@NonNull DocumentViewHolder holder, int position) {
        Document document = documents.get(position);
        holder.bind(document);
    }

    /**
     * Returnează numărul total de documente din listă
     * 
     * @return Numărul de documente
     */
    @Override
    public int getItemCount() {
        return documents.size();
    }

    /**
     * Actualizează lista de documente și notifică RecyclerView
     * 
     * Această metodă permite actualizarea listei de documente
     * și reface afișarea întregii liste.
     * 
     * @param newDocuments Noua listă de documente
     */
    public void updateDocuments(List<Document> newDocuments) {
        this.documents = newDocuments;
        notifyDataSetChanged();
    }

    /**
     * ViewHolder pentru afișarea unui document în RecyclerView
     * 
     * Această clasă internă gestionează referințele către elementele
     * UI ale unui element din listă și leagă datele documentului
     * de aceste elemente.
     */
    class DocumentViewHolder extends RecyclerView.ViewHolder {
        /**
         * TextView pentru iconița documentului
         */
        TextView documentIcon;
        
        /**
         * TextView pentru titlul documentului
         */
        TextView documentTitle;
        
        /**
         * TextView pentru tipul documentului
         */
        TextView documentType;
        
        /**
         * TextView pentru data încărcării
         */
        TextView documentDate;
        
        /**
         * Buton pentru descărcarea documentului
         */
        Button downloadButton;
        
        /**
         * Buton pentru ștergerea documentului
         */
        Button deleteButton;

        /**
         * Constructor pentru ViewHolder
         * 
         * Inițializează referințele către elementele UI din layout-ul
         * item_document.xml.
         * 
         * @param itemView View-ul rădăcină al elementului din listă
         */
        DocumentViewHolder(@NonNull View itemView) {
            super(itemView);
            documentIcon = itemView.findViewById(R.id.document_icon);
            documentTitle = itemView.findViewById(R.id.document_title);
            documentType = itemView.findViewById(R.id.document_type);
            documentDate = itemView.findViewById(R.id.document_date);
            downloadButton = itemView.findViewById(R.id.download_button);
            deleteButton = itemView.findViewById(R.id.delete_button);
        }

        /**
         * Leagă datele documentului de elementele UI
         * 
         * Această metodă populează toate elementele UI cu datele
         * documentului și setează listener-ele pentru butoane.
         * 
         * @param document Documentul de afișat
         */
        void bind(Document document) {
            // Setează iconița documentului
            documentIcon.setText(document.getDocumentIcon());
            
            // Setează titlul documentului
            documentTitle.setText(document.getTitle());
            
            // Setează tipul documentului formatat
            documentType.setText(document.getFormattedDocumentType());
            
            // Formatează și setează data încărcării
            try {
                SimpleDateFormat inputFormat = new SimpleDateFormat("yyyy-MM-dd HH:mm:ss", Locale.getDefault());
                SimpleDateFormat outputFormat = new SimpleDateFormat("dd.MM.yyyy HH:mm", Locale.getDefault());
                String formattedDate = outputFormat.format(inputFormat.parse(document.getUploadDate()));
                documentDate.setText(formattedDate);
            } catch (Exception e) {
                // În caz de eroare la parsarea datei, afișează data originală
                documentDate.setText(document.getUploadDate());
            }

            // Setează listener pentru butonul de descărcare
            downloadButton.setOnClickListener(v -> {
                if (listener != null) {
                    listener.onDownloadDocument(document);
                }
            });

            // Setează listener pentru butonul de ștergere
            deleteButton.setOnClickListener(v -> {
                if (listener != null) {
                    listener.onDeleteDocument(document);
                }
            });
        }
    }
} 
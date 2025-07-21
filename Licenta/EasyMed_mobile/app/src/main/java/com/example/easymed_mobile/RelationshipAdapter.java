package com.example.easymed_mobile;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Button;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;
import java.util.List;

/**
 * Adapter pentru afișarea listei de relații între pacienți în RecyclerView
 * 
 * Această clasă gestionează afișarea relațiilor între pacienți într-o listă interactivă.
 * Fiecare element din listă afișează tipul relației, numele pacientului,
 * CNP-ul și oferă un buton pentru ștergerea relației.
 * 
 * <p>Adapter-ul implementează pattern-ul ViewHolder pentru optimizarea
 * performanței și folosește un listener pentru gestionarea acțiunii de ștergere.</p>
 * 
 * @author EasyMed Mobile Team
 * @version 1.0
 * @since 2024
 */
public class RelationshipAdapter extends RecyclerView.Adapter<RelationshipAdapter.ViewHolder> {
    /**
     * Lista de relații de afișat
     */
    private List<Relationship> relationships;
    
    /**
     * Listener pentru acțiunea de ștergere
     */
    private OnDeleteClickListener deleteListener;

    /**
     * Interfață pentru gestionarea acțiunii de ștergere
     */
    public interface OnDeleteClickListener {
        /**
         * Metodă apelată când utilizatorul dorește să șteargă o relație
         * 
         * @param relationship Relația de șters
         */
        void onDeleteClick(Relationship relationship);
    }

    /**
     * Constructor pentru crearea adapter-ului
     * 
     * @param relationships Lista de relații de afișat
     * @param deleteListener Listener-ul pentru gestionarea ștergerii
     */
    public RelationshipAdapter(List<Relationship> relationships, OnDeleteClickListener deleteListener) {
        this.relationships = relationships;
        this.deleteListener = deleteListener;
    }

    /**
     * Creează un nou ViewHolder pentru un element din listă
     * 
     * @param parent ViewGroup-ul părinte
     * @param viewType Tipul de view (nu este folosit în acest adapter)
     * @return Un nou ViewHolder
     */
    @NonNull
    @Override
    public ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext())
                .inflate(R.layout.item_relationship, parent, false);
        return new ViewHolder(view);
    }

    /**
     * Leagă datele unei relații de ViewHolder-ul corespunzător
     * 
     * @param holder ViewHolder-ul de populat
     * @param position Poziția relației în listă
     */
    @Override
    public void onBindViewHolder(@NonNull ViewHolder holder, int position) {
        Relationship relationship = relationships.get(position);
        
        // Determine the relationship type to display
        String relationshipType = determineRelationshipType(relationship);
        holder.relationshipType.setText(relationshipType);
        
        holder.patientName.setText(relationship.getFullName());
        holder.patientCnp.setText("CNP: " + relationship.getCnp());
        
        holder.deleteButton.setOnClickListener(v -> {
            if (deleteListener != null) {
                deleteListener.onDeleteClick(relationship);
            }
        });
    }

    /**
     * Returnează numărul total de relații din listă
     * 
     * @return Numărul de relații
     */
    @Override
    public int getItemCount() {
        return relationships.size();
    }

    /**
     * Determină tipul de relație pentru afișare
     * 
     * Această metodă procesează tipul de relație stocat în baza de date
     * și îl returnează într-un format potrivit pentru afișare.
     * 
     * @param relationship Relația pentru care se determină tipul
     * @return Tipul de relație formatat pentru afișare
     */
    private String determineRelationshipType(Relationship relationship) {
        // This logic should match the web version's relationship display logic
        // For now, we'll show the relationship type as stored
        return relationship.getTipRelatie();
    }

    /**
     * Actualizează lista de relații și notifică RecyclerView
     * 
     * @param newRelationships Noua listă de relații
     */
    public void updateRelationships(List<Relationship> newRelationships) {
        this.relationships = newRelationships;
        notifyDataSetChanged();
    }

    /**
     * ViewHolder pentru afișarea unei relații în RecyclerView
     * 
     * Această clasă internă gestionează referințele către elementele
     * UI ale unui element din listă și leagă datele relației
     * de aceste elemente.
     */
    static class ViewHolder extends RecyclerView.ViewHolder {
        /**
         * TextView pentru tipul de relație
         */
        TextView relationshipType;
        
        /**
         * TextView pentru numele pacientului
         */
        TextView patientName;
        
        /**
         * TextView pentru CNP-ul pacientului
         */
        TextView patientCnp;
        
        /**
         * Buton pentru ștergerea relației
         */
        Button deleteButton;

        /**
         * Constructor pentru ViewHolder
         * 
         * @param itemView View-ul rădăcină al elementului din listă
         */
        ViewHolder(View itemView) {
            super(itemView);
            relationshipType = itemView.findViewById(R.id.relationship_type);
            patientName = itemView.findViewById(R.id.patient_name);
            patientCnp = itemView.findViewById(R.id.patient_cnp);
            deleteButton = itemView.findViewById(R.id.delete_button);
        }
    }
} 
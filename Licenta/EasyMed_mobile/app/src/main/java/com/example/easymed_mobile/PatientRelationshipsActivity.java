package com.example.easymed_mobile;

import android.app.AlertDialog;
import android.content.Intent;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.EditText;
import android.widget.ImageButton;
import android.widget.Spinner;
import android.widget.TextView;
import android.widget.Toast;
import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;
import com.google.gson.Gson;
import okhttp3.MediaType;
import okhttp3.RequestBody;
import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;
import java.util.ArrayList;
import java.util.List;
import android.app.Dialog;
import android.view.Window;
import android.view.WindowManager;
import android.text.Editable;
import android.text.TextWatcher;

/**
 * Activitate pentru gestionarea relațiilor între pacienți în EasyMed
 *
 * Această activitate permite:
 * - Vizualizarea relațiilor existente ale unui pacient
 * - Adăugarea de noi relații cu alți pacienți
 * - Ștergerea relațiilor existente
 * - Selectarea tipului de relație (părinte, copil, soț, etc.)
 * - Căutarea și selectarea pacienților pentru relații
 *
 * <p>Folosește API-ul EasyMed pentru a încărca și gestiona relațiile
 * între pacienți în sistem.</p>
 *
 * @author EasyMed Mobile Team
 * @version 1.0
 * @since 2024
 */
public class PatientRelationshipsActivity extends AppCompatActivity implements RelationshipAdapter.OnDeleteClickListener {
    /**
     * TextView pentru afișarea pacientului selectat
     */
    private TextView selectedPatientText, noRelationshipsText;
    
    /**
     * Butoane pentru acțiuni principale
     */
    private Button selectPatientButton, addRelationshipButton;
    
    /**
     * Spinner pentru selectarea tipului de relație
     */
    private Spinner relationshipTypeSpinner;
    
    /**
     * RecyclerView pentru afișarea listei de relații
     */
    private RecyclerView relationshipsRecyclerView;
    
    /**
     * Buton pentru navigarea înapoi
     */
    private TextView backButton;
    
    /**
     * ID-ul pacientului curent
     */
    private int currentPatientId;
    
    /**
     * Pacientul selectat pentru relație
     */
    private Patient selectedPatient;
    
    /**
     * Lista de tipuri de relații disponibile
     */
    private List<RelationshipType> relationshipTypes;
    
    /**
     * Lista tuturor pacienților disponibili
     */
    private List<Patient> allPatients;
    
    /**
     * Lista relațiilor curente
     */
    private List<Relationship> relationships;
    
    /**
     * Adapter pentru afișarea relațiilor
     */
    private RelationshipAdapter relationshipAdapter;

    /**
     * Metodă apelată la crearea activității
     *
     * Inițializează interfața, preia ID-ul pacientului din intent,
     * încarcă datele necesare și configurează listener-ele.
     *
     * @param savedInstanceState Starea salvată a activității (poate fi null)
     */
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_patient_relationships);

        // Get patient ID from intent
        currentPatientId = getIntent().getIntExtra("patient_id", 0);
        if (currentPatientId == 0) {
            Toast.makeText(this, "ID pacient lipsă!", Toast.LENGTH_LONG).show();
            finish();
            return;
        }

        initializeViews();
        loadRelationshipTypes();
        loadPatients();
        loadRelationships();
    }

    /**
     * Inițializează elementele UI și configurează listener-ele
     */
    private void initializeViews() {
        selectedPatientText = findViewById(R.id.selected_patient_text);
        selectPatientButton = findViewById(R.id.select_patient_button);
        relationshipTypeSpinner = findViewById(R.id.relationship_type_spinner);
        addRelationshipButton = findViewById(R.id.add_relationship_button);
        relationshipsRecyclerView = findViewById(R.id.relationships_recycler_view);
        noRelationshipsText = findViewById(R.id.no_relationships_text);
        backButton = findViewById(R.id.back_button);

        backButton.setOnClickListener(v -> finish());
        selectPatientButton.setOnClickListener(v -> showPatientSelectionDialog());
        addRelationshipButton.setOnClickListener(v -> addRelationship());

        // Setup RecyclerView
        relationshipsRecyclerView.setLayoutManager(new LinearLayoutManager(this));
        relationships = new ArrayList<>();
        relationshipAdapter = new RelationshipAdapter(relationships, this);
        relationshipsRecyclerView.setAdapter(relationshipAdapter);

        TextView profileIcon = findViewById(R.id.profile_icon);
        profileIcon.setOnClickListener(v -> {
            Intent intent = new Intent(this, ProfileActivity.class);
            startActivity(intent);
        });
    }

    /**
     * Încarcă tipurile de relații disponibile de la server
     */
    private void loadRelationshipTypes() {
        ApiService apiService = ApiClient.getClient().create(ApiService.class);
        apiService.getRelationshipTypes(currentPatientId).enqueue(new Callback<List<RelationshipType>>() {
            @Override
            public void onResponse(Call<List<RelationshipType>> call, Response<List<RelationshipType>> response) {
                if (response.isSuccessful() && response.body() != null) {
                    relationshipTypes = response.body();
                    setupRelationshipTypeSpinner();
                } else {
                    Toast.makeText(PatientRelationshipsActivity.this, 
                        "Eroare la încărcarea tipurilor de relații.", Toast.LENGTH_LONG).show();
                }
            }

            @Override
            public void onFailure(Call<List<RelationshipType>> call, Throwable t) {
                Toast.makeText(PatientRelationshipsActivity.this, 
                    "Eroare de conexiune.", Toast.LENGTH_LONG).show();
            }
        });
    }

    /**
     * Încarcă lista tuturor pacienților disponibili de la server
     */
    private void loadPatients() {
        ApiService apiService = ApiClient.getClient().create(ApiService.class);
        apiService.getPatients(currentPatientId).enqueue(new Callback<List<Patient>>() {
            @Override
            public void onResponse(Call<List<Patient>> call, Response<List<Patient>> response) {
                if (response.isSuccessful() && response.body() != null) {
                    allPatients = response.body();
                } else {
                    Toast.makeText(PatientRelationshipsActivity.this, 
                        "Eroare la încărcarea pacienților.", Toast.LENGTH_LONG).show();
                }
            }

            @Override
            public void onFailure(Call<List<Patient>> call, Throwable t) {
                Toast.makeText(PatientRelationshipsActivity.this, 
                    "Eroare de conexiune.", Toast.LENGTH_LONG).show();
            }
        });
    }

    /**
     * Încarcă relațiile existente ale pacientului curent
     */
    private void loadRelationships() {
        ApiService apiService = ApiClient.getClient().create(ApiService.class);
        apiService.getRelationships(currentPatientId).enqueue(new Callback<List<Relationship>>() {
            @Override
            public void onResponse(Call<List<Relationship>> call, Response<List<Relationship>> response) {
                if (response.isSuccessful() && response.body() != null) {
                    relationships = response.body();
                    updateRelationshipsDisplay();
                } else {
                    Toast.makeText(PatientRelationshipsActivity.this, 
                        "Eroare la încărcarea relațiilor.", Toast.LENGTH_LONG).show();
                }
            }

            @Override
            public void onFailure(Call<List<Relationship>> call, Throwable t) {
                Toast.makeText(PatientRelationshipsActivity.this, 
                    "Eroare de conexiune.", Toast.LENGTH_LONG).show();
            }
        });
    }

    /**
     * Configurează spinner-ul pentru tipurile de relații
     */
    private void setupRelationshipTypeSpinner() {
        if (relationshipTypes != null) {
            List<String> typeNames = new ArrayList<>();
            typeNames.add("Selectează tipul de relație");
            
            for (RelationshipType type : relationshipTypes) {
                typeNames.add(type.getDenumire());
            }
            
            ArrayAdapter<String> adapter = new ArrayAdapter<String>(this, android.R.layout.simple_spinner_item, typeNames) {
                @Override
                public View getView(int position, View convertView, android.view.ViewGroup parent) {
                    View view = super.getView(position, convertView, parent);
                    TextView textView = (TextView) view.findViewById(android.R.id.text1);
                    if (textView != null) textView.setTextColor(getResources().getColor(android.R.color.white));
                    return view;
                }
                @Override
                public View getDropDownView(int position, View convertView, android.view.ViewGroup parent) {
                    View view = super.getDropDownView(position, convertView, parent);
                    TextView textView = (TextView) view.findViewById(android.R.id.text1);
                    if (textView != null) textView.setTextColor(getResources().getColor(android.R.color.white));
                    return view;
                }
            };
            adapter.setDropDownViewResource(R.layout.spinner_dropdown_dark);
            relationshipTypeSpinner.setAdapter(adapter);
        }
    }

    /**
     * Afișează dialogul pentru selectarea unui pacient
     */
    private void showPatientSelectionDialog() {
        if (allPatients == null || allPatients.isEmpty()) {
            Toast.makeText(this, "Nu există pacienți disponibili.", Toast.LENGTH_LONG).show();
            return;
        }

        Dialog dialog = new Dialog(this);
        dialog.requestWindowFeature(Window.FEATURE_NO_TITLE);
        dialog.setContentView(R.layout.dialog_select_patient);

        // Set dialog width to match screen width
        Window window = dialog.getWindow();
        if (window != null) {
            WindowManager.LayoutParams layoutParams = new WindowManager.LayoutParams();
            layoutParams.copyFrom(window.getAttributes());
            layoutParams.width = WindowManager.LayoutParams.MATCH_PARENT;
            layoutParams.height = WindowManager.LayoutParams.WRAP_CONTENT;
            window.setAttributes(layoutParams);
        }

        // UI references
        EditText searchInput = dialog.findViewById(R.id.search_patient_input);
        RecyclerView patientListRecyclerView = dialog.findViewById(R.id.patient_list_recycler_view);
        TextView selectedPatientPreview = dialog.findViewById(R.id.selected_patient_preview);
        Button closeDialogButton = dialog.findViewById(R.id.close_dialog_button);
        Button confirmPatientButton = dialog.findViewById(R.id.confirm_patient_button);

        // Setup patient list
        patientListRecyclerView.setLayoutManager(new LinearLayoutManager(this));
        PatientDialogAdapter patientAdapter = new PatientDialogAdapter(allPatients, patient -> {
            selectedPatient = patient;
            selectedPatientPreview.setText("Selectat: " + patient.getFullName());
        });
        patientListRecyclerView.setAdapter(patientAdapter);

        // Search functionality
        searchInput.addTextChangedListener(new TextWatcher() {
            @Override public void beforeTextChanged(CharSequence s, int start, int count, int after) {}
            @Override public void onTextChanged(CharSequence s, int start, int before, int count) {}
            @Override public void afterTextChanged(Editable s) {
                String query = s.toString().toLowerCase();
                List<Patient> filteredPatients = new ArrayList<>();
                for (Patient patient : allPatients) {
                    if (patient.getFullName().toLowerCase().contains(query) ||
                        patient.getCnp().contains(query)) {
                        filteredPatients.add(patient);
                    }
                }
                patientAdapter.updatePatients(filteredPatients);
            }
        });

        closeDialogButton.setOnClickListener(v -> dialog.dismiss());
        confirmPatientButton.setOnClickListener(v -> {
            if (selectedPatient != null) {
                selectedPatientText.setText(selectedPatient.getFullName());
            }
            dialog.dismiss();
        });

        dialog.show();
    }

    /**
     * Adaugă o nouă relație între pacienți
     */
    private void addRelationship() {
        if (selectedPatient == null) {
            Toast.makeText(this, "Selectați un pacient!", Toast.LENGTH_LONG).show();
            return;
        }

        if (relationshipTypeSpinner.getSelectedItemPosition() == 0) {
            Toast.makeText(this, "Selectați tipul de relație!", Toast.LENGTH_LONG).show();
            return;
        }

        int selectedTypeIndex = relationshipTypeSpinner.getSelectedItemPosition() - 1;
        RelationshipType selectedType = relationshipTypes.get(selectedTypeIndex);

        RelationshipRequest request = new RelationshipRequest(
            currentPatientId, 
            selectedPatient.getId(), 
            selectedType.getId()
        );

        ApiService apiService = ApiClient.getClient().create(ApiService.class);
        String jsonRequest = new Gson().toJson(request);
        RequestBody body = RequestBody.create(MediaType.parse("application/json"), jsonRequest);

        apiService.addRelationship(body).enqueue(new Callback<RelationshipResponse>() {
            @Override
            public void onResponse(Call<RelationshipResponse> call, Response<RelationshipResponse> response) {
                if (response.isSuccessful() && response.body() != null && response.body().isSuccess()) {
                    Toast.makeText(PatientRelationshipsActivity.this, 
                        "Relația a fost adăugată cu succes!", Toast.LENGTH_LONG).show();
                    resetForm();
                    loadRelationships();
                } else {
                    String errorMsg = "Eroare la adăugarea relației.";
                    if (response.body() != null && response.body().getError() != null) {
                        errorMsg = response.body().getError();
                    }
                    Toast.makeText(PatientRelationshipsActivity.this, errorMsg, Toast.LENGTH_LONG).show();
                }
            }

            @Override
            public void onFailure(Call<RelationshipResponse> call, Throwable t) {
                Toast.makeText(PatientRelationshipsActivity.this, 
                    "Eroare de conexiune.", Toast.LENGTH_LONG).show();
            }
        });
    }

    /**
     * Resetează formularul de adăugare relație
     */
    private void resetForm() {
        selectedPatient = null;
        selectedPatientText.setText("Selectați un pacient");
        relationshipTypeSpinner.setSelection(0);
    }

    /**
     * Actualizează afișarea listei de relații
     */
    private void updateRelationshipsDisplay() {
        if (relationships.isEmpty()) {
            noRelationshipsText.setVisibility(View.VISIBLE);
            relationshipsRecyclerView.setVisibility(View.GONE);
        } else {
            noRelationshipsText.setVisibility(View.GONE);
            relationshipsRecyclerView.setVisibility(View.VISIBLE);
            relationshipAdapter.updateRelationships(relationships);
        }
    }

    /**
     * Metodă apelată când utilizatorul dorește să șteargă o relație
     * 
     * @param relationship Relația de șters
     */
    @Override
    public void onDeleteClick(Relationship relationship) {
        new AlertDialog.Builder(this)
            .setTitle("Ștergere relație")
            .setMessage("Sigur doriți să ștergeți această relație?")
            .setPositiveButton("Da", (dialog, which) -> deleteRelationship(relationship))
            .setNegativeButton("Nu", null)
            .show();
    }

    /**
     * Șterge o relație de la server
     * 
     * @param relationship Relația de șters
     */
    private void deleteRelationship(Relationship relationship) {
        DeleteRelationshipRequest request = new DeleteRelationshipRequest(
            relationship.getId(), 
            currentPatientId
        );

        ApiService apiService = ApiClient.getClient().create(ApiService.class);
        String jsonRequest = new Gson().toJson(request);
        RequestBody body = RequestBody.create(MediaType.parse("application/json"), jsonRequest);

        apiService.deleteRelationship(body).enqueue(new Callback<RelationshipResponse>() {
            @Override
            public void onResponse(Call<RelationshipResponse> call, Response<RelationshipResponse> response) {
                if (response.isSuccessful() && response.body() != null && response.body().isSuccess()) {
                    Toast.makeText(PatientRelationshipsActivity.this, 
                        "Relația a fost ștearsă cu succes!", Toast.LENGTH_LONG).show();
                    loadRelationships();
                } else {
                    String errorMsg = "Eroare la ștergerea relației.";
                    if (response.body() != null && response.body().getError() != null) {
                        errorMsg = response.body().getError();
                    }
                    Toast.makeText(PatientRelationshipsActivity.this, errorMsg, Toast.LENGTH_LONG).show();
                }
            }

            @Override
            public void onFailure(Call<RelationshipResponse> call, Throwable t) {
                Toast.makeText(PatientRelationshipsActivity.this, 
                    "Eroare de conexiune.", Toast.LENGTH_LONG).show();
            }
        });
    }

    /**
     * Clasă internă pentru cererea de adăugare relație
     */
    private static class RelationshipRequest {
        private int pacient_id;
        private int pacient_relat_id;
        private int tip_relatie_id;

        public RelationshipRequest(int pacientId, int pacientRelatId, int tipRelatieId) {
            this.pacient_id = pacientId;
            this.pacient_relat_id = pacientRelatId;
            this.tip_relatie_id = tipRelatieId;
        }
    }

    /**
     * Clasă internă pentru cererea de ștergere relație
     */
    private static class DeleteRelationshipRequest {
        private int relatie_id;
        private int pacient_id;

        public DeleteRelationshipRequest(int relatieId, int pacientId) {
            this.relatie_id = relatieId;
            this.pacient_id = pacientId;
        }
    }
} 
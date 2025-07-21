package com.example.easymed_mobile;

import android.content.Intent;
import android.os.Bundle;
import android.text.Editable;
import android.text.TextWatcher;
import android.util.Log;
import android.view.View;
import android.widget.AdapterView;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.CheckBox;
import android.widget.EditText;
import android.widget.LinearLayout;
import android.widget.ProgressBar;
import android.widget.Spinner;
import android.widget.TextView;
import android.widget.Toast;
import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;
import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;
import java.util.ArrayList;
import java.util.List;
import android.view.inputmethod.EditorInfo;
import android.view.inputmethod.InputMethodManager;

/**
 * Activitate pentru afișarea și filtrarea listei de medici în EasyMed
 *
 * Această activitate permite:
 * - Căutarea medicilor după nume sau specializare
 * - Filtrarea și sortarea listei de medici
 * - Vizualizarea detaliilor despre fiecare medic
 * - Navigarea către profilul utilizatorului
 *
 * <p>Folosește API-ul EasyMed pentru a încărca lista medicilor și specializărilor.</p>
 *
 * @author EasyMed Mobile Team
 * @version 1.0
 * @since 2024
 */
public class ListaMediciActivity extends AppCompatActivity implements DoctorAdapter.OnDoctorClickListener {
    
    private static final String TAG = "ListaMediciActivity";
    
    // UI Components
    private EditText searchInput;
    private Spinner specialtySpinner;
    private CheckBox myDoctorsCheckbox;
    private Button sortNameButton, sortSpecialtyButton;
    private RecyclerView doctorsRecyclerView;
    private TextView noResultsText;
    private ProgressBar loadingIndicator;
    private TextView backButton;
    
    // Profile dropdown
    private LinearLayout dropdownMenu;
    private TextView profileIcon, profileOption, logoutOption;
    
    // Data
    private List<Doctor> allDoctors = new ArrayList<>();
    private List<String> specialties = new ArrayList<>();
    private DoctorAdapter doctorAdapter;
    
    // Filter and sort state
    private String currentSearch = "";
    private String currentSpecialty = "";
    private boolean showOnlyMyDoctors = false;
    private String currentSort = "nume";
    private String currentOrder = "ASC";

    /**
     * Metodă apelată la crearea activității
     *
     * Inițializează interfața, configurează lista de medici, spinner-ul de specializări și butoanele principale.
     *
     * @param savedInstanceState Starea salvată a activității (poate fi null)
     */
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_lista_medici);
        
        initializeViews();
        setupProfileDropdown();
        setupRecyclerView();
        setupListeners();
        loadSpecialties();
        loadDoctors();
    }

    private void initializeViews() {
        searchInput = findViewById(R.id.search_input);
        specialtySpinner = findViewById(R.id.specialty_spinner);
        myDoctorsCheckbox = findViewById(R.id.my_doctors_checkbox);
        sortNameButton = findViewById(R.id.sort_name_button);
        sortSpecialtyButton = findViewById(R.id.sort_specialty_button);
        doctorsRecyclerView = findViewById(R.id.doctors_recycler_view);
        noResultsText = findViewById(R.id.no_results_text);
        loadingIndicator = findViewById(R.id.loading_indicator);
        backButton = findViewById(R.id.back_button);
        
        // Profile dropdown
        profileIcon = findViewById(R.id.profile_icon);
        profileIcon.setOnClickListener(v -> {
            Intent intent = new Intent(this, ProfileActivity.class);
            startActivity(intent);
        });
    }

    private void setupProfileDropdown() {
        // The dropdown menu and its options are removed as per the edit hint.
        // The profileIcon now directly navigates to ProfileActivity.
    }

    private void setupRecyclerView() {
        doctorAdapter = new DoctorAdapter(new ArrayList<>(), this);
        doctorsRecyclerView.setLayoutManager(new LinearLayoutManager(this));
        doctorsRecyclerView.setAdapter(doctorAdapter);
    }

    private void setupListeners() {
        // Back button
        backButton.setOnClickListener(v -> {
            finish();
        });

        // Search input
        searchInput.addTextChangedListener(new TextWatcher() {
            @Override
            public void beforeTextChanged(CharSequence s, int start, int count, int after) {}

            @Override
            public void onTextChanged(CharSequence s, int start, int before, int count) {}

            @Override
            public void afterTextChanged(Editable s) {
                currentSearch = s.toString();
                // Optionally, you can debounce this call if you want to avoid too many API calls
                // loadDoctors();
            }
        });
        // Trigger search on keyboard action (Enter/Done)
        searchInput.setOnEditorActionListener((v, actionId, event) -> {
            if (actionId == EditorInfo.IME_ACTION_SEARCH || actionId == EditorInfo.IME_ACTION_DONE) {
                currentSearch = searchInput.getText().toString();
                loadDoctors();
                // Hide keyboard
                InputMethodManager imm = (InputMethodManager) getSystemService(INPUT_METHOD_SERVICE);
                if (imm != null) {
                    imm.hideSoftInputFromWindow(searchInput.getWindowToken(), 0);
                }
                return true;
            }
            return false;
        });

        // Specialty spinner
        specialtySpinner.setOnItemSelectedListener(new AdapterView.OnItemSelectedListener() {
            @Override
            public void onItemSelected(AdapterView<?> parent, View view, int position, long id) {
                if (position > 0) {
                    currentSpecialty = specialties.get(position - 1);
                } else {
                    currentSpecialty = "";
                }
                loadDoctors();
            }

            @Override
            public void onNothingSelected(AdapterView<?> parent) {
                currentSpecialty = "";
                loadDoctors();
            }
        });

        // My doctors checkbox
        myDoctorsCheckbox.setOnCheckedChangeListener((buttonView, isChecked) -> {
            showOnlyMyDoctors = isChecked;
            loadDoctors();
        });

        // Sort buttons
        sortNameButton.setOnClickListener(v -> {
            if (currentSort.equals("nume")) {
                currentOrder = currentOrder.equals("ASC") ? "DESC" : "ASC";
            } else {
                currentSort = "nume";
                currentOrder = "ASC";
            }
            updateSortButtons();
            loadDoctors();
        });

        sortSpecialtyButton.setOnClickListener(v -> {
            if (currentSort.equals("specializare")) {
                currentOrder = currentOrder.equals("ASC") ? "DESC" : "ASC";
            } else {
                currentSort = "specializare";
                currentOrder = "ASC";
            }
            updateSortButtons();
            loadDoctors();
        });
    }

    private void updateSortButtons() {
        // Update name button
        String nameText = "Nume " + (currentSort.equals("nume") ? 
            (currentOrder.equals("ASC") ? "↑" : "↓") : "↑");
        sortNameButton.setText(nameText);
        sortNameButton.setBackgroundColor(currentSort.equals("nume") ? 
            getResources().getColor(android.R.color.holo_green_light) : 
            getResources().getColor(android.R.color.darker_gray));

        // Update specialty button
        String specialtyText = "Specializare " + (currentSort.equals("specializare") ? 
            (currentOrder.equals("ASC") ? "↑" : "↓") : "↑");
        sortSpecialtyButton.setText(specialtyText);
        sortSpecialtyButton.setBackgroundColor(currentSort.equals("specializare") ? 
            getResources().getColor(android.R.color.holo_green_light) : 
            getResources().getColor(android.R.color.darker_gray));
    }

    private void loadSpecialties() {
        ApiService apiService = ApiClient.getClient().create(ApiService.class);
        Call<List<String>> call = apiService.getSpecialties();
        
        call.enqueue(new Callback<List<String>>() {
            @Override
            public void onResponse(Call<List<String>> call, Response<List<String>> response) {
                if (response.isSuccessful() && response.body() != null) {
                    specialties = response.body();
                    setupSpecialtySpinner();
                } else {
                    Log.e(TAG, "Error loading specialties: " + response.code());
                    Toast.makeText(ListaMediciActivity.this, 
                        "Eroare la încărcarea specializărilor", Toast.LENGTH_SHORT).show();
                }
            }

            @Override
            public void onFailure(Call<List<String>> call, Throwable t) {
                Log.e(TAG, "Network error loading specialties", t);
                Toast.makeText(ListaMediciActivity.this, 
                    "Eroare de rețea la încărcarea specializărilor", Toast.LENGTH_SHORT).show();
            }
        });
    }

    private void setupSpecialtySpinner() {
        List<String> spinnerItems = new ArrayList<>();
        spinnerItems.add("Toate specializările");
        spinnerItems.addAll(specialties);

        ArrayAdapter<String> adapter = new ArrayAdapter<>(this,
            R.layout.spinner_item_dark, spinnerItems);
        adapter.setDropDownViewResource(R.layout.spinner_dropdown_item_dark);
        specialtySpinner.setAdapter(adapter);
    }

    private void loadDoctors() {
        showLoading(true);
        
        ApiService apiService = ApiClient.getClient().create(ApiService.class);
        Call<DoctorsResponse> call = apiService.getDoctors(
            currentSearch.isEmpty() ? null : currentSearch,
            currentSpecialty.isEmpty() ? null : currentSpecialty,
            showOnlyMyDoctors,
            currentSort,
            currentOrder
        );
        
        call.enqueue(new Callback<DoctorsResponse>() {
            @Override
            public void onResponse(Call<DoctorsResponse> call, Response<DoctorsResponse> response) {
                showLoading(false);
                if (response.isSuccessful() && response.body() != null) {
                    DoctorsResponse doctorsResponse = response.body();
                    if (doctorsResponse.isSuccess()) {
                        allDoctors = doctorsResponse.getDoctors();
                        updateDoctorsList();
                    } else {
                        Log.e(TAG, "API Error: " + doctorsResponse.getError());
                        Toast.makeText(ListaMediciActivity.this, 
                            "Eroare API: " + doctorsResponse.getError(), Toast.LENGTH_SHORT).show();
                    }
                } else {
                    Log.e(TAG, "Error loading doctors: " + response.code());
                    Toast.makeText(ListaMediciActivity.this, 
                        "Eroare la încărcarea medicilor", Toast.LENGTH_SHORT).show();
                }
            }

            @Override
            public void onFailure(Call<DoctorsResponse> call, Throwable t) {
                showLoading(false);
                Log.e(TAG, "Network error loading doctors", t);
                Toast.makeText(ListaMediciActivity.this, 
                    "Eroare de rețea la încărcarea medicilor", Toast.LENGTH_SHORT).show();
            }
        });
    }

    private void updateDoctorsList() {
        if (allDoctors.isEmpty()) {
            doctorsRecyclerView.setVisibility(View.GONE);
            noResultsText.setVisibility(View.VISIBLE);
        } else {
            doctorsRecyclerView.setVisibility(View.VISIBLE);
            noResultsText.setVisibility(View.GONE);
            doctorAdapter.updateDoctors(allDoctors);
        }
    }

    private void showLoading(boolean show) {
        loadingIndicator.setVisibility(show ? View.VISIBLE : View.GONE);
        doctorsRecyclerView.setVisibility(show ? View.GONE : View.VISIBLE);
        noResultsText.setVisibility(View.GONE);
    }

    @Override
    public void onDoctorClick(Doctor doctor) {
        Intent intent = new Intent(this, ProgramareActivity.class);
        intent.putExtra("doctor_id", doctor.getId());
        intent.putExtra("doctor_name", doctor.getFullName());
        intent.putExtra("doctor_specialty", doctor.getSpecializare());
        startActivity(intent);
    }
} 
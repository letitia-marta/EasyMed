package com.example.easymed_mobile;

import android.app.AlertDialog;
import android.content.Intent;
import android.graphics.Color;
import android.graphics.drawable.GradientDrawable;
import android.net.Uri;
import android.os.Bundle;
import android.os.Environment;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.Button;
import android.widget.DatePicker;
import android.widget.EditText;
import android.widget.LinearLayout;
import android.widget.Spinner;
import android.widget.TextView;
import android.widget.Toast;
import androidx.annotation.Nullable;
import androidx.appcompat.app.AppCompatActivity;
import java.text.SimpleDateFormat;
import java.util.ArrayList;
import java.util.Calendar;
import java.util.List;
import java.util.Locale;
import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;
import android.view.ViewGroup;
import android.util.Log;
import android.widget.FrameLayout;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;
import okhttp3.MediaType;
import okhttp3.MultipartBody;
import okhttp3.RequestBody;
import java.io.File;
import java.io.FileOutputStream;
import java.io.IOException;
import java.io.InputStream;
import android.content.ContentResolver;
import android.webkit.MimeTypeMap;
import android.widget.ArrayAdapter;
import org.json.JSONObject;
import org.json.JSONException;

/**
 * Activitate pentru gestionarea istoricului medical al pacientului în EasyMed
 *
 * Această activitate permite:
 * - Vizualizarea programărilor viitoare ale pacientului
 * - Vizualizarea consultațiilor anterioare
 * - Gestionarea documentelor medicale (încărcare, descărcare, ștergere)
 * - Analiza automată a documentelor cu AI pentru sugestii de titlu și tip
 * - Editarea și ștergerea programărilor
 * - Testarea conexiunii cu serverul
 *
 * <p>Folosește API-ul EasyMed pentru toate operațiunile cu datele
 * și implementează funcționalități avansate de gestionare a documentelor.</p>
 *
 * @author EasyMed Mobile Team
 * @version 1.0
 * @since 2024
 */
public class IstoricMedicalActivity extends AppCompatActivity implements DocumentAdapter.OnDocumentActionListener {
    /**
     * Container-e pentru afișarea programărilor și consultațiilor
     */
    private LinearLayout appointmentsContainer, consultationsContainer;
    
    /**
     * Text-uri pentru cazurile când nu există date
     */
    private TextView noAppointmentsText, noConsultationsText, backButton;
    
    /**
     * ID-ul pacientului curent
     */
    private int patientId; // Should be set from session or intent
    
    // Document-related variables
    /**
     * Cod pentru cererea de selectare fișier
     */
    private static final int PICK_FILE_REQUEST_CODE = 1;
    
    /**
     * URI-ul fișierului selectat
     */
    private Uri selectedFileUri;
    
    /**
     * Datele fișierului selectat în memorie
     */
    private byte[] selectedFileData; // Store file data in memory
    
    /**
     * Numele fișierului selectat
     */
    private String selectedFileName;
    
    /**
     * TextView pentru afișarea numelui fișierului selectat
     */
    private TextView selectedFileNameView;
    
    /**
     * Input pentru titlul documentului
     */
    private EditText documentTitleInput;
    
    /**
     * Spinner pentru tipul documentului
     */
    private Spinner documentTypeSpinner;
    
    /**
     * Butoane pentru acțiuni cu documente
     */
    private Button selectFileButton, analyzeButton, uploadDocumentButton;
    
    /**
     * Container-e pentru sugestiile AI
     */
    private LinearLayout aiTitleSuggestionContainer, aiTypeSuggestionContainer;
    
    /**
     * Text-uri pentru sugestiile AI
     */
    private TextView aiTitleSuggestion, aiTypeSuggestion;
    
    /**
     * Butoane pentru folosirea sugestiilor AI
     */
    private Button useTitleSuggestion, useTypeSuggestion;
    
    /**
     * RecyclerView pentru afișarea documentelor
     */
    private RecyclerView documentsRecyclerView;
    
    /**
     * Text pentru cazul când nu există documente
     */
    private TextView noDocumentsText;
    
    /**
     * Adapter pentru afișarea documentelor
     */
    private DocumentAdapter documentAdapter;
    
    /**
     * Lista documentelor
     */
    private List<Document> documents = new ArrayList<>();

    /**
     * Metodă apelată la crearea activității
     *
     * Inițializează interfața, preia ID-ul pacientului din intent,
     * încarcă datele necesare și configurează funcționalitățile.
     *
     * @param savedInstanceState Starea salvată a activității (poate fi null)
     */
    @Override
    protected void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_istoric_medical);

        // TODO: Replace with actual patient ID from session/login
        patientId = getIntent().getIntExtra("patient_id", 0);
        if (patientId == 0) {
            Toast.makeText(this, "ID pacient lipsă!", Toast.LENGTH_LONG).show();
            finish();
            return;
        }

        appointmentsContainer = findViewById(R.id.upcoming_appointments_container);
        consultationsContainer = findViewById(R.id.consultations_container);
        noAppointmentsText = findViewById(R.id.no_appointments_text);
        noConsultationsText = findViewById(R.id.no_consultations_text);
        backButton = findViewById(R.id.back_button);

        backButton.setOnClickListener(v -> finish());

        TextView profileIcon = findViewById(R.id.profile_icon);
        profileIcon.setOnClickListener(v -> {
            Intent intent = new Intent(this, ProfileActivity.class);
            intent.putExtra("patient_id", patientId);
            startActivity(intent);
        });

        loadAppointments();
        loadConsultations();
        
        // Initialize document functionality
        initializeDocumentViews();
        loadDocuments();
        
        // Test API connection
        testApiConnection();
    }

    /**
     * Încarcă programările viitoare ale pacientului
     */
    private void loadAppointments() {
        ApiService apiService = ApiClient.getClient().create(ApiService.class);
        apiService.getUpcomingAppointments(patientId).enqueue(new Callback<List<Appointment>>() {
            @Override
            public void onResponse(Call<List<Appointment>> call, Response<List<Appointment>> response) {
                if (response.isSuccessful() && response.body() != null && !response.body().isEmpty()) {
                    displayAppointments(response.body());
                    noAppointmentsText.setVisibility(View.GONE);
                } else {
                    appointmentsContainer.removeAllViews();
                    noAppointmentsText.setVisibility(View.VISIBLE);
                }
            }
            @Override
            public void onFailure(Call<List<Appointment>> call, Throwable t) {
                appointmentsContainer.removeAllViews();
                noAppointmentsText.setVisibility(View.VISIBLE);
            }
        });
    }

    /**
     * Afișează programările în interfață
     * 
     * @param appointments Lista de programări de afișat
     */
    private void displayAppointments(List<Appointment> appointments) {
        appointmentsContainer.removeAllViews();
        LayoutInflater inflater = LayoutInflater.from(this);
        
        for (Appointment appointment : appointments) {
            View appointmentView = inflater.inflate(R.layout.item_appointment, appointmentsContainer, false);
            
            TextView date = appointmentView.findViewById(R.id.appointment_date);
            TextView time = appointmentView.findViewById(R.id.appointment_time);
            TextView doctor = appointmentView.findViewById(R.id.appointment_doctor);
            TextView specialty = appointmentView.findViewById(R.id.appointment_specialty);
            TextView reason = appointmentView.findViewById(R.id.appointment_reason);
            Button editBtn = appointmentView.findViewById(R.id.appointment_edit_btn);
            Button deleteBtn = appointmentView.findViewById(R.id.appointment_delete_btn);
            
            // Format date as dd.mm.yyyy
            date.setText(formatDate(appointment.getDate()));
            // Format time as hh:mm
            time.setText(formatTime(appointment.getTimeSlot()));
            doctor.setText(appointment.getDoctorName());
            specialty.setText(appointment.getSpecialty());
            reason.setText(appointment.getConsultationType());
            
            editBtn.setOnClickListener(v -> {
                Log.d("IstoricMedical", "Edit button clicked for appointment ID: " + appointment.getId());

                showEditAppointmentDialog(appointment);
            });
            
            deleteBtn.setOnClickListener(v -> {
                showDeleteConfirmationDialog(appointment);
            });
            
            appointmentsContainer.addView(appointmentView);
        }
    }

    /**
     * Afișează dialogul pentru editarea unei programări
     * 
     * @param appointment Programarea de editat
     */
    private void showEditAppointmentDialog(Appointment appointment) {
        Log.d("IstoricMedical", "showEditAppointmentDialog called for appointment ID: " + appointment.getId());
        
        // Save current locale
        Locale currentLocale = Locale.getDefault();
        
        try {
            // Set Romanian locale for the DatePicker
            Locale.setDefault(new Locale("ro", "RO"));
            
            AlertDialog.Builder builder = new AlertDialog.Builder(this);
            
            // Step 1: Inflate the layout
            Log.d("IstoricMedical", "Step 1: Inflating layout");
            LayoutInflater inflater = getLayoutInflater();
            View dialogView = inflater.inflate(R.layout.dialog_edit_appointment, null);
            Log.d("IstoricMedical", "Step 1: Layout inflated successfully");
            
            // Step 2: Find all views
            Log.d("IstoricMedical", "Step 2: Finding views");
            DatePicker datePicker = dialogView.findViewById(R.id.date_picker);
            FrameLayout timeSlotsContainer = dialogView.findViewById(R.id.time_slots_container);
            Button saveButton = dialogView.findViewById(R.id.save_button);
            Button cancelButton = dialogView.findViewById(R.id.cancel_button);
            
            Log.d("IstoricMedical", "Step 2: Views found - datePicker=" + (datePicker != null) + 
                  ", timeSlotsContainer=" + (timeSlotsContainer != null) + 
                  ", saveButton=" + (saveButton != null) + 
                  ", cancelButton=" + (cancelButton != null));
            
            if (saveButton == null) {
                Log.e("IstoricMedical", "Save button is null! This is a problem.");
                // Try to find it by ID directly
                saveButton = dialogView.findViewById(R.id.save_button);
                Log.d("IstoricMedical", "Retry finding save button: " + (saveButton != null ? "found" : "still null"));
            } else {
                Log.d("IstoricMedical", "Save button found successfully with text: " + saveButton.getText());
            }
            
            if (cancelButton == null) {
                Log.e("IstoricMedical", "Cancel button is null! This is a problem.");
                // Try to find it by ID directly
                cancelButton = dialogView.findViewById(R.id.cancel_button);
                Log.d("IstoricMedical", "Retry finding cancel button: " + (cancelButton != null ? "found" : "still null"));
            } else {
                Log.d("IstoricMedical", "Cancel button found successfully with text: " + cancelButton.getText());
            }
            
            // Step 3: Set up variables
            Log.d("IstoricMedical", "Step 3: Setting up variables");
            final String[] selectedDate = {appointment.getDate()};
            final String[] selectedTime = {appointment.getTimeSlot()};
            
            // Step 4: Configure DatePicker
            Log.d("IstoricMedical", "Step 4: Configuring DatePicker");
            Calendar today = Calendar.getInstance();
            datePicker.setMinDate(today.getTimeInMillis());
            
            try {
                SimpleDateFormat sdf = new SimpleDateFormat("yyyy-MM-dd", Locale.getDefault());
                Calendar appointmentDate = Calendar.getInstance();
                appointmentDate.setTime(sdf.parse(appointment.getDate()));
                datePicker.updateDate(appointmentDate.get(Calendar.YEAR), 
                                   appointmentDate.get(Calendar.MONTH), 
                                   appointmentDate.get(Calendar.DAY_OF_MONTH));
                Log.d("IstoricMedical", "Step 4: DatePicker configured successfully");
            } catch (Exception e) {
                Log.e("IstoricMedical", "Error parsing appointment date: " + appointment.getDate(), e);
            }
            
            // Step 5: Create dialog
            Log.d("IstoricMedical", "Step 5: Creating dialog");
            builder.setView(dialogView);
            builder.setTitle("Modifică Programarea");
            AlertDialog dialog = builder.create();
            Log.d("IstoricMedical", "Step 5: Dialog created successfully");
            
            // Step 6: Set up DatePicker listener
            Log.d("IstoricMedical", "Step 6: Setting up DatePicker listener");
            datePicker.init(datePicker.getYear(), datePicker.getMonth(), datePicker.getDayOfMonth(),
                new DatePicker.OnDateChangedListener() {
                    @Override
                    public void onDateChanged(DatePicker view, int year, int monthOfYear, int dayOfMonth) {
                        selectedDate[0] = String.format(Locale.getDefault(), "%04d-%02d-%02d", year, monthOfYear + 1, dayOfMonth);
                        // Reset selected time when date changes
                        selectedTime[0] = "";
                        loadTimeSlots(selectedDate[0], appointment.getMedicId(), timeSlotsContainer, appointment, selectedTime);
                    }
                });
            
            // Step 7: Load initial time slots
            Log.d("IstoricMedical", "Step 7: Loading initial time slots");
            String currentDate = appointment.getDate();
            loadTimeSlots(currentDate, appointment.getMedicId(), timeSlotsContainer, appointment, selectedTime);
            
            // Step 8: Set up button listeners
            Log.d("IstoricMedical", "Step 8: Setting up button listeners");
            Log.d("IstoricMedical", "Setting up save button listener for button: " + (saveButton != null ? "found" : "null"));
            saveButton.setOnClickListener(v -> {
                Log.d("IstoricMedical", "Save button clicked");
                if (selectedTime[0] == null || selectedTime[0].isEmpty()) {
                    Toast.makeText(this, "Vă rugăm să selectați o oră.", Toast.LENGTH_SHORT).show();
                    return;
                }
                
                // Show loading dialog
                AlertDialog loadingDialog = new AlertDialog.Builder(this)
                    .setMessage("Se salvează modificările...")
                    .setCancelable(false)
                    .create();
                loadingDialog.show();
                
                // Call API to update appointment
                Log.d("IstoricMedical", "Calling updateAppointment API with: appointmentId=" + appointment.getId() + 
                      ", patientId=" + patientId + ", medicId=" + appointment.getMedicId() + 
                      ", date=" + selectedDate[0] + ", time=" + selectedTime[0]);
                
                ApiService apiService = ApiClient.getClient().create(ApiService.class);
                apiService.updateAppointment(
                    appointment.getId(),
                    patientId,
                    appointment.getMedicId(),
                    selectedDate[0],
                    selectedTime[0],
                    "programat",
                    appointment.getConsultationType()
                ).enqueue(new Callback<UpdateAppointmentResponse>() {
                    @Override
                    public void onResponse(Call<UpdateAppointmentResponse> call, Response<UpdateAppointmentResponse> response) {
                        loadingDialog.dismiss();
                        dialog.dismiss();
                        
                        Log.d("IstoricMedical", "Response received. Success: " + response.isSuccessful() + 
                              ", Code: " + response.code() + ", Body: " + (response.body() != null ? "not null" : "null"));
                        
                        if (response.isSuccessful()) {
                            if (response.body() != null) {
                            UpdateAppointmentResponse updateResponse = response.body();
                            Log.d("IstoricMedical", "Update response - success: " + updateResponse.isSuccess() + 
                                  ", message: " + updateResponse.getMessage() + 
                                  ", error: " + updateResponse.getError());
                            
                            if (updateResponse.isSuccess()) {
                                Toast.makeText(IstoricMedicalActivity.this, 
                                    "Programarea a fost actualizată cu succes.", Toast.LENGTH_LONG).show();
                                // Reload appointments to show updated data
                                loadAppointments();
                            } else {
                                String errorMsg = updateResponse.getError() != null ? 
                                    updateResponse.getError() : "Eroare la actualizarea programării.";
                                Toast.makeText(IstoricMedicalActivity.this, errorMsg, Toast.LENGTH_LONG).show();
                            }
                        } else {
                            Log.e("IstoricMedical", "Response body is null");
                            Toast.makeText(IstoricMedicalActivity.this, 
                                "Eroare la actualizarea programării.", Toast.LENGTH_LONG).show();
                        }
                    } else {
                            // Try to get the raw response for debugging
                            try {
                                if (response.errorBody() != null) {
                                    String errorBody = response.errorBody().string();
                                    Log.e("IstoricMedical", "Error response body: " + errorBody);
                                }
                            } catch (Exception e) {
                                Log.e("IstoricMedical", "Could not get error response", e);
                            }
                            
                            Toast.makeText(IstoricMedicalActivity.this, 
                                "Eroare la actualizarea programării.", Toast.LENGTH_LONG).show();
                        }
                    }
                    
                    @Override
                    public void onFailure(Call<UpdateAppointmentResponse> call, Throwable t) {
                        loadingDialog.dismiss();
                        dialog.dismiss();
                        Log.e("IstoricMedical", "API call failed", t);
                        
                        // Try to get the raw response for debugging
                        try {
                            if (call.isExecuted()) {
                                retrofit2.Response<UpdateAppointmentResponse> response = call.execute();
                                if (response.errorBody() != null) {
                                    String errorBody = response.errorBody().string();
                                    Log.e("IstoricMedical", "Error response body: " + errorBody);
                                }
                            }
                        } catch (Exception e) {
                            Log.e("IstoricMedical", "Could not get error response", e);
                        }
                        
                        Toast.makeText(IstoricMedicalActivity.this, 
                            "Eroare de conexiune. Vă rugăm să încercați din nou.", Toast.LENGTH_LONG).show();
                    }
                });
            });
            
            cancelButton.setOnClickListener(v -> {
                Log.d("IstoricMedical", "Cancel button clicked");
                dialog.dismiss();
            });
            
            // Step 9: Show dialog
            Log.d("IstoricMedical", "Step 9: About to show dialog");
            dialog.show();
            Log.d("IstoricMedical", "Step 9: Dialog shown successfully");
            
        } catch (Exception e) {
            Log.e("IstoricMedical", "Error creating edit dialog", e);
            Toast.makeText(this, "Eroare la deschiderea dialogului de editare: " + e.getMessage(), Toast.LENGTH_LONG).show();
        } finally {
            // Restore original locale
            Locale.setDefault(currentLocale);
        }
    }
    
    private void loadTimeSlots(String date, int medicId, FrameLayout container, Appointment appointment, String[] selectedTime) {
        // Show loading message first
        container.removeAllViews();
        TextView loadingText = new TextView(this);
        loadingText.setText("Încărcare sloturi disponibile...");
        loadingText.setTextColor(getResources().getColor(android.R.color.white));
        loadingText.setPadding(16, 16, 16, 16);
        container.addView(loadingText);
        Log.d("IstoricMedical", "Loading time slots for date: " + date + ", medicId: " + medicId);
        
        ApiService apiService = ApiClient.getClient().create(ApiService.class);
        apiService.getAvailableTimeSlots(medicId, date).enqueue(new Callback<List<AvailableTimeSlot>>() {
            @Override
            public void onResponse(Call<List<AvailableTimeSlot>> call, Response<List<AvailableTimeSlot>> response) {
                Log.d("IstoricMedical", "Time slots response received. Success: " + response.isSuccessful() + ", Body: " + (response.body() != null ? response.body().size() : "null"));
                if (response.isSuccessful() && response.body() != null) {
                    displayTimeSlots(response.body(), container, appointment, selectedTime);
                } else {
                    container.removeAllViews();
                    TextView errorText = new TextView(IstoricMedicalActivity.this);
                    errorText.setText("Eroare la încărcarea sloturilor disponibile.");
                    errorText.setTextColor(getResources().getColor(android.R.color.white));
                    errorText.setPadding(16, 16, 16, 16);
                    container.addView(errorText);
                }
            }
            
            @Override
            public void onFailure(Call<List<AvailableTimeSlot>> call, Throwable t) {
                Log.e("IstoricMedical", "Error loading time slots", t);
                container.removeAllViews();
                TextView errorText = new TextView(IstoricMedicalActivity.this);
                errorText.setText("Eroare la încărcarea sloturilor disponibile.");
                errorText.setTextColor(getResources().getColor(android.R.color.white));
                errorText.setPadding(16, 16, 16, 16);
                container.addView(errorText);
            }
        });
    }
    
    private void displayTimeSlots(List<AvailableTimeSlot> timeSlots, FrameLayout container, Appointment appointment, String[] selectedTime) {
        Log.d("IstoricMedical", "Displaying " + timeSlots.size() + " time slots");
        
        // Create a RecyclerView to hold the time slot buttons
        RecyclerView timeSlotsRecyclerView = new RecyclerView(this);
        timeSlotsRecyclerView.setLayoutManager(new LinearLayoutManager(this)); // Changed from GridLayoutManager to LinearLayoutManager
        timeSlotsRecyclerView.setPadding(16, 16, 16, 16);
        
        // Create adapter with the same logic as ProgramareActivity
        TimeSlotAdapter adapter = new TimeSlotAdapter(timeSlots, selectedTime[0], timeSlot -> {
            selectedTime[0] = timeSlot;
        });
        
        timeSlotsRecyclerView.setAdapter(adapter);
        
        // Replace the container's content with the RecyclerView
        container.removeAllViews();
        Log.d("IstoricMedical", "Adding new RecyclerView with " + timeSlots.size() + " slots");
        container.addView(timeSlotsRecyclerView);
    }
    
    private void loadConsultations() {
        ApiService apiService = ApiClient.getClient().create(ApiService.class);
        apiService.getConsultations(patientId).enqueue(new Callback<List<Consultation>>() {
            @Override
            public void onResponse(Call<List<Consultation>> call, Response<List<Consultation>> response) {
                if (response.isSuccessful() && response.body() != null && !response.body().isEmpty()) {
                    displayConsultations(response.body());
                    noConsultationsText.setVisibility(View.GONE);
                } else {
                    consultationsContainer.removeAllViews();
                    noConsultationsText.setVisibility(View.VISIBLE);
                }
            }
            @Override
            public void onFailure(Call<List<Consultation>> call, Throwable t) {
                consultationsContainer.removeAllViews();
                noConsultationsText.setVisibility(View.VISIBLE);
            }
        });
    }

    private void displayConsultations(List<Consultation> consultations) {
        consultationsContainer.removeAllViews();
        LayoutInflater inflater = LayoutInflater.from(this);
        
        for (Consultation consultation : consultations) {
            View consultationView = inflater.inflate(R.layout.item_consultation, consultationsContainer, false);
            
            TextView date = consultationView.findViewById(R.id.consultation_date);
            TextView doctor = consultationView.findViewById(R.id.consultation_doctor);
            TextView diagnosis = consultationView.findViewById(R.id.consultation_diagnosis);
            Button detailsBtn = consultationView.findViewById(R.id.consultation_details_btn);
            
            date.setText(formatDate(consultation.getDate()));
            doctor.setText(consultation.getDoctor_name() + "\n" + consultation.getSpecialty());
            diagnosis.setText(consultation.getDiagnosis());
            
            detailsBtn.setOnClickListener(v -> {
                Intent intent = new Intent(IstoricMedicalActivity.this, ConsultationDetailsActivity.class);
                intent.putExtra("consultation_id", consultation.getId());
                startActivity(intent);
            });
            
            consultationsContainer.addView(consultationView);
        }
    }

    private String formatDate(String input) {
        try {
            SimpleDateFormat inputFormat = new SimpleDateFormat("yyyy-MM-dd", Locale.getDefault());
            SimpleDateFormat outputFormat = new SimpleDateFormat("dd.MM.yyyy", Locale.getDefault());
            return outputFormat.format(inputFormat.parse(input));
        } catch (Exception e) {
            return input != null ? input : "";
        }
    }
    
    private String formatTime(String input) {
        try {
            SimpleDateFormat inputFormat = new SimpleDateFormat("HH:mm:ss", Locale.getDefault());
            SimpleDateFormat outputFormat = new SimpleDateFormat("HH:mm", Locale.getDefault());
            return outputFormat.format(inputFormat.parse(input));
        } catch (Exception e) {
            // If parsing fails, try to extract time from the string
            if (input != null && input.contains(":")) {
                String[] parts = input.split(":");
                if (parts.length >= 2) {
                    return parts[0] + ":" + parts[1];
                }
            }
            return input != null ? input : "";
        }
    }
    
    private void testApiConnection() {
        Log.d("IstoricMedical", "Testing API connection...");
        ApiService apiService = ApiClient.getClient().create(ApiService.class);
        apiService.debugResponse().enqueue(new Callback<TestConnectionResponse>() {
            @Override
            public void onResponse(Call<TestConnectionResponse> call, Response<TestConnectionResponse> response) {
                if (response.isSuccessful() && response.body() != null) {
                    TestConnectionResponse testResponse = response.body();
                    Log.d("IstoricMedical", "API connection test successful: " + testResponse.getMessage());
                } else {
                    Log.e("IstoricMedical", "API connection test failed: " + response.code());
                }
            }
            
            @Override
            public void onFailure(Call<TestConnectionResponse> call, Throwable t) {
                Log.e("IstoricMedical", "API connection test failed", t);
            }
        });
    }
    
    private void showDeleteConfirmationDialog(Appointment appointment) {
        new AlertDialog.Builder(this)
            .setTitle("Confirmare ștergere")
            .setMessage("Sigur doriți să ștergeți această programare? Această acțiune nu poate fi anulată.")
            .setPositiveButton("Șterge", (dialog, which) -> {
                deleteAppointment(appointment);
            })
            .setNegativeButton("Anulează", null)
            .show();
    }
    
    private void deleteAppointment(Appointment appointment) {
        // Show loading dialog
        AlertDialog loadingDialog = new AlertDialog.Builder(this)
            .setMessage("Se șterge programarea...")
            .setCancelable(false)
            .create();
        loadingDialog.show();
        
        Log.d("IstoricMedical", "Deleting appointment ID: " + appointment.getId());
        
        ApiService apiService = ApiClient.getClient().create(ApiService.class);
        apiService.deleteAppointment(appointment.getId()).enqueue(new Callback<DeleteAppointmentResponse>() {
            @Override
            public void onResponse(Call<DeleteAppointmentResponse> call, Response<DeleteAppointmentResponse> response) {
                loadingDialog.dismiss();
                
                Log.d("IstoricMedical", "Delete response received. Success: " + response.isSuccessful() + 
                      ", Code: " + response.code() + ", Body: " + (response.body() != null ? "not null" : "null"));
                
                if (response.isSuccessful()) {
                    if (response.body() != null) {
                        DeleteAppointmentResponse deleteResponse = response.body();
                        Log.d("IstoricMedical", "Delete response - success: " + deleteResponse.isSuccess() + 
                              ", message: " + deleteResponse.getMessage() + 
                              ", error: " + deleteResponse.getError());
                        
                        if (deleteResponse.isSuccess()) {
                            Toast.makeText(IstoricMedicalActivity.this, 
                                "Programarea a fost ștearsă cu succes.", Toast.LENGTH_LONG).show();
                            // Reload appointments to show updated data
                            loadAppointments();
                        } else {
                            String errorMsg = deleteResponse.getError() != null ? 
                                deleteResponse.getError() : "Eroare la ștergerea programării.";
                            Toast.makeText(IstoricMedicalActivity.this, errorMsg, Toast.LENGTH_LONG).show();
                        }
                    } else {
                        Log.e("IstoricMedical", "Delete response body is null");
                        Toast.makeText(IstoricMedicalActivity.this, 
                            "Eroare la ștergerea programării.", Toast.LENGTH_LONG).show();
                    }
                } else {
                    // Try to get the raw response for debugging
                    try {
                        if (response.errorBody() != null) {
                            String errorBody = response.errorBody().string();
                            Log.e("IstoricMedical", "Delete error response body: " + errorBody);
                        }
                    } catch (Exception e) {
                        Log.e("IstoricMedical", "Could not get delete error response", e);
                    }
                    
                    Toast.makeText(IstoricMedicalActivity.this, 
                        "Eroare la ștergerea programării.", Toast.LENGTH_LONG).show();
                }
            }
            
            @Override
            public void onFailure(Call<DeleteAppointmentResponse> call, Throwable t) {
                loadingDialog.dismiss();
                Log.e("IstoricMedical", "Delete API call failed", t);
                Toast.makeText(IstoricMedicalActivity.this, 
                    "Eroare de conexiune. Vă rugăm să încercați din nou.", Toast.LENGTH_LONG).show();
            }
        });
    }

    // Document-related methods
    private void initializeDocumentViews() {
        selectedFileNameView = findViewById(R.id.selected_file_name);
        documentTitleInput = findViewById(R.id.document_title_input);
        documentTypeSpinner = findViewById(R.id.document_type_spinner);
        selectFileButton = findViewById(R.id.select_file_button);
        analyzeButton = findViewById(R.id.analyze_button);
        uploadDocumentButton = findViewById(R.id.upload_document_button);
        aiTitleSuggestionContainer = findViewById(R.id.ai_title_suggestion_container);
        aiTypeSuggestionContainer = findViewById(R.id.ai_type_suggestion_container);
        aiTitleSuggestion = findViewById(R.id.ai_title_suggestion);
        aiTypeSuggestion = findViewById(R.id.ai_type_suggestion);
        useTitleSuggestion = findViewById(R.id.use_title_suggestion);
        useTypeSuggestion = findViewById(R.id.use_type_suggestion);
        documentsRecyclerView = findViewById(R.id.documents_recycler_view);
        noDocumentsText = findViewById(R.id.no_documents_text);

        // Set up document type spinner
        String[] documentTypes = {
            "Selectează tipul",
            "Analize medicale",
            "Imagistica medicala", 
            "Foaie de observatie",
            "Scrisoare medicala",
            "Bilet de externare",
            "Altele"
        };
        String[] documentTypeValues = {
            "",
            "analize",
            "imagistica_medicala",
            "observatie", 
            "scrisori",
            "externari",
            "alte"
        };
        
        ArrayAdapter<String> spinnerAdapter = new ArrayAdapter<>(this, android.R.layout.simple_spinner_item, documentTypes) {
            @Override
            public View getView(int position, View convertView, ViewGroup parent) {
                View view = super.getView(position, convertView, parent);
                if (view instanceof TextView) {
                    ((TextView) view).setTextColor(Color.WHITE);
                    view.setBackgroundColor(Color.parseColor("#1E1E1E")); // Dark background
                }
                return view;
            }

            @Override
            public View getDropDownView(int position, View convertView, ViewGroup parent) {
                View view = super.getDropDownView(position, convertView, parent);
                if (view instanceof TextView) {
                    ((TextView) view).setTextColor(Color.WHITE);
                    view.setBackgroundColor(Color.parseColor("#1E1E1E")); // Dark background
                }
                return view;
            }
        };
        spinnerAdapter.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item);
        documentTypeSpinner.setAdapter(spinnerAdapter);
        
        // Set the spinner background to dark
        documentTypeSpinner.setBackgroundColor(Color.parseColor("#1E1E1E"));

        // Set up RecyclerView
        documentsRecyclerView.setLayoutManager(new LinearLayoutManager(this));
        documentAdapter = new DocumentAdapter(this, documents, this);
        documentsRecyclerView.setAdapter(documentAdapter);

        // Set up click listeners
        selectFileButton.setOnClickListener(v -> selectFile());
        analyzeButton.setOnClickListener(v -> analyzeDocument());
        uploadDocumentButton.setOnClickListener(v -> uploadDocument());
        useTitleSuggestion.setOnClickListener(v -> useTitleSuggestion());
        useTypeSuggestion.setOnClickListener(v -> useTypeSuggestion());
    }

    private void selectFile() {
        Intent intent = new Intent(Intent.ACTION_GET_CONTENT);
        intent.setType("*/*");
        intent.addCategory(Intent.CATEGORY_OPENABLE);
        intent.putExtra(Intent.EXTRA_MIME_TYPES, new String[]{
            "application/pdf",
            "image/jpeg",
            "image/png", 
            "application/msword",
            "application/vnd.openxmlformats-officedocument.wordprocessingml.document"
        });
        startActivityForResult(Intent.createChooser(intent, "Selectează un document"), PICK_FILE_REQUEST_CODE);
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, @Nullable Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        
        if (requestCode == PICK_FILE_REQUEST_CODE && resultCode == RESULT_OK && data != null) {
            selectedFileUri = data.getData();
            if (selectedFileUri != null) {
                selectedFileName = getFileName(selectedFileUri);
                selectedFileNameView.setText(selectedFileName);
                
                // Store file data in memory
                try {
                    selectedFileData = readFileDataFromUri(selectedFileUri);
                    Log.d("FileSelection", "File data stored in memory, size: " + selectedFileData.length);
                    analyzeButton.setEnabled(true);
                } catch (Exception e) {
                    Log.e("FileSelection", "Error reading file data", e);
                    Toast.makeText(this, "Eroare la citirea fișierului: " + e.getMessage(), Toast.LENGTH_LONG).show();
                    selectedFileUri = null;
                    selectedFileData = null;
                    selectedFileName = null;
                    selectedFileNameView.setText("Nu a fost selectat niciun fișier");
                    analyzeButton.setEnabled(false);
                }
            }
        }
    }

    private String getFileName(Uri uri) {
        String result = null;
        if (uri.getScheme().equals("content")) {
            try (android.database.Cursor cursor = getContentResolver().query(uri, null, null, null, null)) {
                if (cursor != null && cursor.moveToFirst()) {
                    int index = cursor.getColumnIndex("_display_name");
                    if (index >= 0) {
                        result = cursor.getString(index);
                    }
                }
            }
        }
        if (result == null) {
            result = uri.getPath();
            int cut = result.lastIndexOf('/');
            if (cut != -1) {
                result = result.substring(cut + 1);
            }
        }
        return result;
    }

    private byte[] readFileDataFromUri(Uri uri) throws IOException {
        Log.d("FileSelection", "Reading file data from URI: " + uri.toString());
        
        InputStream inputStream = getContentResolver().openInputStream(uri);
        if (inputStream == null) {
            throw new IOException("Could not open input stream for URI: " + uri);
        }
        
        try {
            // Read all data into byte array
            byte[] data = new byte[inputStream.available()];
            int bytesRead = inputStream.read(data);
            
            if (bytesRead == -1) {
                throw new IOException("No data read from file");
            }
            
            // If we didn't read all available data, read the rest
            if (bytesRead < data.length) {
                byte[] actualData = new byte[bytesRead];
                System.arraycopy(data, 0, actualData, 0, bytesRead);
                data = actualData;
            }
            
            Log.d("FileSelection", "Successfully read " + data.length + " bytes");
            return data;
            
        } finally {
            inputStream.close();
        }
    }

    private void analyzeDocument() {
        if (selectedFileData == null) {
            Toast.makeText(this, "Vă rugăm să selectați un fișier mai întâi.", Toast.LENGTH_SHORT).show();
            return;
        }

        analyzeButton.setText("🤖 Analizez...");
        analyzeButton.setEnabled(false);

        try {
            File file = createTempFileFromData(selectedFileData, selectedFileName);
            Log.d("DocumentAnalysis", "Created temp file from data: " + file.getAbsolutePath() + ", size: " + file.length());
            
            String mimeType = getMimeType(selectedFileUri);
            Log.d("DocumentAnalysis", "MIME type: " + mimeType);
            
            if (mimeType == null) {
                mimeType = "application/octet-stream";
                Log.d("DocumentAnalysis", "Using fallback MIME type: " + mimeType);
            }
            
            RequestBody requestFile = RequestBody.create(MediaType.parse(mimeType), file);
            MultipartBody.Part filePart = MultipartBody.Part.createFormData("document_file", file.getName(), requestFile);
            Log.d("DocumentAnalysis", "MultipartBody.Part created, file size: " + file.length());

            ApiService apiService = ApiClient.getClient().create(ApiService.class);
            Log.d("DocumentAnalysis", "Making API call to test_ai_comparison.php");
            Log.d("DocumentAnalysis", "File part name: " + file.getName());
            Log.d("DocumentAnalysis", "File part size: " + file.length());
            Log.d("DocumentAnalysis", "MIME type: " + mimeType);
            
            apiService.analyzeDocument(filePart).enqueue(new Callback<DocumentAnalysisResponse>() {
                @Override
                public void onResponse(Call<DocumentAnalysisResponse> call, Response<DocumentAnalysisResponse> response) {
                    Log.d("DocumentAnalysis", "Response received - Success: " + response.isSuccessful() + ", Code: " + response.code());
                    
                    // Log raw response for debugging
                    try {
                        if (response.errorBody() != null) {
                            String errorBody = response.errorBody().string();
                            Log.e("DocumentAnalysis", "Raw error response: " + errorBody);
                        }
                        
                        // Also try to get the raw response body for successful responses
                        if (response.isSuccessful()) {
                            try {
                                okhttp3.Response rawResponse = response.raw();
                                if (rawResponse.body() != null) {
                                    String rawBody = rawResponse.body().string();
                                    Log.d("DocumentAnalysis", "Raw successful response body: " + rawBody);
                                }
                            } catch (Exception e) {
                                Log.e("DocumentAnalysis", "Could not read raw response body", e);
                            }
                        }
                    } catch (IOException e) {
                        Log.e("DocumentAnalysis", "Could not read error body", e);
                    }
                    
                    // Log successful response body
                    if (response.isSuccessful() && response.body() != null) {
                        DocumentAnalysisResponse analysis = response.body();
                        Log.d("DocumentAnalysis", "=== ANALYSIS RESPONSE ===");
                        Log.d("DocumentAnalysis", "Success: " + analysis.isSuccess());
                        Log.d("DocumentAnalysis", "Title: " + analysis.getSuggestedTitle());
                        Log.d("DocumentAnalysis", "Type: " + analysis.getDocumentType());
                        Log.d("DocumentAnalysis", "Confidence: " + analysis.getConfidence());
                        Log.d("DocumentAnalysis", "Error: " + analysis.getError());
                        Log.d("DocumentAnalysis", "=== END ANALYSIS RESPONSE ===");
                        
                        if (analysis.isSuccess()) {
                            showAISuggestions(analysis);
                        } else {
                            String errorMsg = analysis.getError() != null ? analysis.getError() : "Eroare necunoscută";
                            Log.e("DocumentAnalysis", "Analysis failed: " + errorMsg);
                            Toast.makeText(IstoricMedicalActivity.this, 
                                "Eroare la analizarea documentului: " + errorMsg, 
                                Toast.LENGTH_LONG).show();
                        }
                    } else {
                        String errorBody = "";
                        try {
                            if (response.errorBody() != null) {
                                errorBody = response.errorBody().string();
                                Log.e("DocumentAnalysis", "Error response body: " + errorBody);
                            }
                        } catch (IOException e) {
                            Log.e("DocumentAnalysis", "Could not read error body", e);
                        }
                        
                        Log.e("DocumentAnalysis", "Response not successful - Code: " + response.code());
                        Toast.makeText(IstoricMedicalActivity.this, 
                            "Eroare la analizarea documentului. Cod: " + response.code() + "\n" + errorBody, 
                            Toast.LENGTH_LONG).show();
                    }
                    analyzeButton.setText("🤖 Analizează cu AI");
                    analyzeButton.setEnabled(true);
                }

                @Override
                public void onFailure(Call<DocumentAnalysisResponse> call, Throwable t) {
                    Log.e("DocumentAnalysis", "Network failure", t);
                    Log.e("DocumentAnalysis", "Failure message: " + t.getMessage());
                    Log.e("DocumentAnalysis", "Failure cause: " + (t.getCause() != null ? t.getCause().getMessage() : "null"));
                    
                    // Check if it's a JSON parsing error
                    if (t.getMessage() != null && t.getMessage().contains("JSON")) {
                        Log.e("DocumentAnalysis", "JSON parsing error detected: " + t.getMessage());
                        Toast.makeText(IstoricMedicalActivity.this, 
                            "Eroare la parsarea răspunsului JSON: " + t.getMessage(), 
                            Toast.LENGTH_LONG).show();
                    } else {
                        Toast.makeText(IstoricMedicalActivity.this, 
                            "Eroare la analizarea documentului: " + t.getMessage(), 
                            Toast.LENGTH_LONG).show();
                    }
                    
                    analyzeButton.setText("🤖 Analizează cu AI");
                    analyzeButton.setEnabled(true);
                }
            });
        } catch (IOException e) {
            Toast.makeText(this, "Eroare la procesarea fișierului: " + e.getMessage(), Toast.LENGTH_LONG).show();
            analyzeButton.setText("🤖 Analizează cu AI");
            analyzeButton.setEnabled(true);
        }
    }

    private void showAISuggestions(DocumentAnalysisResponse analysis) {
        Log.d("DocumentAnalysis", "Showing AI suggestions - Title: " + analysis.getSuggestedTitle() + 
              ", Type: " + analysis.getDocumentType() + ", Confidence: " + analysis.getConfidence());
        
        // Show title suggestion with null check
        String suggestedTitle = analysis.getSuggestedTitle();
        if (suggestedTitle == null || suggestedTitle.isEmpty()) {
            suggestedTitle = "Document Medical";
        }
        aiTitleSuggestion.setText("💡 Sugestie AI: " + suggestedTitle);
        aiTitleSuggestionContainer.setVisibility(View.VISIBLE);

        // Show type suggestion with null check
        String documentType = analysis.getDocumentType();
        if (documentType == null || documentType.isEmpty()) {
            documentType = "alte";
        }
        String typeLabel = getTypeLabel(documentType);
        aiTypeSuggestion.setText("🤖 Tip detectat: " + typeLabel);
        aiTypeSuggestionContainer.setVisibility(View.VISIBLE);

        // Set confidence-based colors with cyan outline
        double confidence = analysis.getConfidence();
        if (confidence <= 0) confidence = 0.5; // Default confidence
        
        // Create cyan outline drawable
        GradientDrawable outlineDrawable = new GradientDrawable();
        outlineDrawable.setShape(GradientDrawable.RECTANGLE);
        outlineDrawable.setStroke(4, Color.CYAN); // 4dp cyan border
        outlineDrawable.setColor(Color.TRANSPARENT); // Transparent background
        
        // Apply the outline to both containers
        aiTitleSuggestionContainer.setBackground(outlineDrawable);
        aiTypeSuggestionContainer.setBackground(outlineDrawable);
        
        Log.d("DocumentAnalysis", "AI suggestions displayed successfully");
    }

    private String getTypeLabel(String type) {
        if (type == null || type.isEmpty()) {
            return "Altele";
        }
        
        switch (type) {
            case "analize": return "Analize medicale";
            case "imagistica_medicala": return "Imagistica medicala";
            case "observatie": return "Foaie de observatie";
            case "scrisori": return "Scrisoare medicala";
            case "externari": return "Bilet de externare";
            case "alte": return "Altele";
            default: return type;
        }
    }

    private void useTitleSuggestion() {
        String suggestedTitle = aiTitleSuggestion.getText().toString().replace("💡 Sugestie AI: ", "");
        documentTitleInput.setText(suggestedTitle);
    }

    private void useTypeSuggestion() {
        String suggestedType = aiTypeSuggestion.getText().toString().replace("🤖 Tip detectat: ", "");
        Log.d("DocumentAnalysis", "Using type suggestion: " + suggestedType);
        
        boolean found = false;
        for (int i = 0; i < documentTypeSpinner.getCount(); i++) {
            String spinnerItem = documentTypeSpinner.getItemAtPosition(i).toString();
            Log.d("DocumentAnalysis", "Checking spinner item " + i + ": " + spinnerItem);
            if (spinnerItem.equals(suggestedType)) {
                documentTypeSpinner.setSelection(i);
                Log.d("DocumentAnalysis", "Type suggestion applied successfully");
                found = true;
                break;
            }
        }
        
        if (!found) {
            Log.w("DocumentAnalysis", "Could not find matching type in spinner for: " + suggestedType);
        }
    }

    private void uploadDocument() {
        if (selectedFileUri == null) {
            Toast.makeText(this, "Vă rugăm să selectați un fișier.", Toast.LENGTH_SHORT).show();
            return;
        }

        String title = documentTitleInput.getText().toString().trim();
        if (title.isEmpty()) {
            Toast.makeText(this, "Vă rugăm să introduceți titlul documentului.", Toast.LENGTH_SHORT).show();
            return;
        }

        String selectedType = documentTypeSpinner.getSelectedItem().toString();
        if (selectedType.equals("Selectează tipul")) {
            Toast.makeText(this, "Vă rugăm să selectați tipul documentului.", Toast.LENGTH_SHORT).show();
            return;
        }
        
        // Map display name to database value
        String type = "";
        switch (selectedType) {
            case "Analize medicale": type = "analize"; break;
            case "Imagistica medicala": type = "imagistica_medicala"; break;
            case "Foaie de observatie": type = "observatie"; break;
            case "Scrisoare medicala": type = "scrisori"; break;
            case "Bilet de externare": type = "externari"; break;
            case "Altele": type = "alte"; break;
            default: type = "alte"; break;
        }
        
        Log.d("DocumentUpload", "Selected type: " + selectedType + ", Mapped to: " + type);

        uploadDocumentButton.setText("Se încarcă...");
        uploadDocumentButton.setEnabled(false);

        try {
            Log.d("DocumentUpload", "Starting file processing...");
            
            // Create temp file from stored data
            File file = createTempFileFromData(selectedFileData, selectedFileName);
            Log.d("DocumentUpload", "Temp file created from data: " + file.getAbsolutePath() + ", size: " + file.length());
            
            String mimeType = getMimeType(selectedFileUri);
            if (mimeType == null) {
                mimeType = "application/octet-stream";
            }
            Log.d("DocumentUpload", "MIME type: " + mimeType);
            
            RequestBody requestFile = RequestBody.create(MediaType.parse(mimeType), file);
            MultipartBody.Part filePart = MultipartBody.Part.createFormData("document_file", file.getName(), requestFile);
            Log.d("DocumentUpload", "MultipartBody.Part created successfully");

            RequestBody titleBody = RequestBody.create(MediaType.parse("text/plain"), title);
            RequestBody typeBody = RequestBody.create(MediaType.parse("text/plain"), type);
            RequestBody patientIdBody = RequestBody.create(MediaType.parse("text/plain"), String.valueOf(patientId));
            Log.d("DocumentUpload", "Request bodies created - title: " + title + ", type: " + type + ", patientId: " + patientId);

            ApiService apiService = ApiClient.getClient().create(ApiService.class);
            Log.d("DocumentUpload", "Making API call to upload document...");
            apiService.uploadDocument(filePart, titleBody, typeBody, patientIdBody).enqueue(new Callback<DocumentUploadResponse>() {
                @Override
                public void onResponse(Call<DocumentUploadResponse> call, Response<DocumentUploadResponse> response) {
                    Log.d("DocumentUpload", "Response received - Success: " + response.isSuccessful() + ", Code: " + response.code());
                    
                    if (response.isSuccessful() && response.body() != null) {
                        DocumentUploadResponse uploadResponse = response.body();
                        Log.d("DocumentUpload", "Upload response - success: " + uploadResponse.isSuccess() + ", message: " + uploadResponse.getMessage());
                        
                        if (uploadResponse.isSuccess()) {
                            Toast.makeText(IstoricMedicalActivity.this, "Document încărcat cu succes!", Toast.LENGTH_LONG).show();
                            clearUploadForm();
                            loadDocuments();
                        } else {
                            String errorMsg = uploadResponse.getMessage() != null ? uploadResponse.getMessage() : "Eroare necunoscută";
                            Toast.makeText(IstoricMedicalActivity.this, 
                                "Eroare la încărcarea documentului: " + errorMsg, 
                                Toast.LENGTH_LONG).show();
                        }
                    } else {
                        String errorBody = "";
                        try {
                            if (response.errorBody() != null) {
                                errorBody = response.errorBody().string();
                                Log.e("DocumentUpload", "Error response body: " + errorBody);
                            }
                        } catch (IOException e) {
                            Log.e("DocumentUpload", "Could not read error body", e);
                        }
                        
                        Toast.makeText(IstoricMedicalActivity.this, 
                            "Eroare la încărcarea documentului. Cod: " + response.code(), 
                            Toast.LENGTH_LONG).show();
                    }
                    uploadDocumentButton.setText("Încarcă Document");
                    uploadDocumentButton.setEnabled(true);
                }

                @Override
                public void onFailure(Call<DocumentUploadResponse> call, Throwable t) {
                    Log.e("DocumentUpload", "Network failure", t);
                    Log.e("DocumentUpload", "Failure message: " + t.getMessage());
                    Log.e("DocumentUpload", "Failure cause: " + (t.getCause() != null ? t.getCause().getMessage() : "null"));
                    
                    String errorMessage = "Eroare la încărcarea documentului";
                    if (t instanceof IllegalStateException) {
                        errorMessage += ": Eroare de stare ilegală - verificați fișierul";
                    } else if (t.getMessage() != null) {
                        errorMessage += ": " + t.getMessage();
                    }
                    
                    Toast.makeText(IstoricMedicalActivity.this, errorMessage, Toast.LENGTH_LONG).show();
                    uploadDocumentButton.setText("Încarcă Document");
                    uploadDocumentButton.setEnabled(true);
                }
            });
        } catch (Exception e) {
            Log.e("DocumentUpload", "Exception during upload preparation", e);
            String errorMessage = "Eroare la procesarea fișierului";
            if (e instanceof IllegalStateException) {
                errorMessage = "Fișierul a fost deja utilizat pentru analiză. Vă rugăm să selectați din nou fișierul pentru încărcare.";
                // Clear the selected file to force user to reselect
                selectedFileUri = null;
                selectedFileData = null;
                selectedFileName = null;
                selectedFileNameView.setText("Nu a fost selectat niciun fișier");
                analyzeButton.setEnabled(false);
            } else if (e.getMessage() != null) {
                errorMessage += ": " + e.getMessage();
            }
            
            Toast.makeText(this, errorMessage, Toast.LENGTH_LONG).show();
            uploadDocumentButton.setText("Încarcă Document");
            uploadDocumentButton.setEnabled(true);
        }
    }

    private void clearUploadForm() {
        selectedFileUri = null;
        selectedFileData = null;
        selectedFileName = null;
        selectedFileNameView.setText("Nu a fost selectat niciun fișier");
        documentTitleInput.setText("");
        documentTypeSpinner.setSelection(0);
        aiTitleSuggestionContainer.setVisibility(View.GONE);
        aiTypeSuggestionContainer.setVisibility(View.GONE);
        analyzeButton.setEnabled(false);
    }

    private void loadDocuments() {
        ApiService apiService = ApiClient.getClient().create(ApiService.class);
        apiService.getDocuments(patientId).enqueue(new Callback<List<Document>>() {
            @Override
            public void onResponse(Call<List<Document>> call, Response<List<Document>> response) {
                if (response.isSuccessful() && response.body() != null) {
                    documents.clear();
                    documents.addAll(response.body());
                    documentAdapter.updateDocuments(documents);
                    
                    if (documents.isEmpty()) {
                        documentsRecyclerView.setVisibility(View.GONE);
                        noDocumentsText.setVisibility(View.VISIBLE);
                    } else {
                        documentsRecyclerView.setVisibility(View.VISIBLE);
                        noDocumentsText.setVisibility(View.GONE);
                    }
                } else {
                    documentsRecyclerView.setVisibility(View.GONE);
                    noDocumentsText.setVisibility(View.VISIBLE);
                }
            }

            @Override
            public void onFailure(Call<List<Document>> call, Throwable t) {
                documentsRecyclerView.setVisibility(View.GONE);
                noDocumentsText.setVisibility(View.VISIBLE);
                Toast.makeText(IstoricMedicalActivity.this, "Eroare la încărcarea documentelor: " + t.getMessage(), Toast.LENGTH_LONG).show();
            }
        });
    }

    @Override
    public void onDownloadDocument(Document document) {
        Log.d("DocumentDownload", "Starting download for document: " + document.getTitle() + ", ID: " + document.getId());
        
        // Show loading dialog
        AlertDialog loadingDialog = new AlertDialog.Builder(this)
            .setMessage("Se descarcă documentul...")
            .setCancelable(false)
            .create();
        loadingDialog.show();
        
        ApiService apiService = ApiClient.getClient().create(ApiService.class);
        apiService.downloadDocument(document.getId()).enqueue(new Callback<okhttp3.ResponseBody>() {
            @Override
            public void onResponse(Call<okhttp3.ResponseBody> call, Response<okhttp3.ResponseBody> response) {
                loadingDialog.dismiss();
                
                if (response.isSuccessful() && response.body() != null) {
                    try {
                        // Get file extension from document title or use default
                        String fileExtension = "pdf"; // default
                        if (document.getTitle().contains(".")) {
                            fileExtension = document.getTitle().substring(document.getTitle().lastIndexOf(".") + 1);
                        }
                        
                        // Create file in Downloads directory
                        File downloadsDir = Environment.getExternalStoragePublicDirectory(Environment.DIRECTORY_DOWNLOADS);
                        if (!downloadsDir.exists()) {
                            downloadsDir.mkdirs();
                        }
                        
                        String fileName = document.getTitle() + "." + fileExtension;
                        File file = new File(downloadsDir, fileName);
                        
                        // Write response body to file
                        FileOutputStream fos = new FileOutputStream(file);
                        fos.write(response.body().bytes());
                        fos.close();
                        
                        Log.d("DocumentDownload", "File downloaded successfully: " + file.getAbsolutePath());
                        
                        Toast.makeText(IstoricMedicalActivity.this, 
                            "Document descărcat cu succes în Downloads: " + fileName, 
                            Toast.LENGTH_LONG).show();
                        
                    } catch (Exception e) {
                        Log.e("DocumentDownload", "Error saving file", e);
                        Toast.makeText(IstoricMedicalActivity.this, 
                            "Eroare la salvarea fișierului: " + e.getMessage(), 
                            Toast.LENGTH_LONG).show();
                    }
                } else {
                    Log.e("DocumentDownload", "Download failed - Code: " + response.code());
                    Toast.makeText(IstoricMedicalActivity.this, 
                        "Eroare la descărcarea documentului. Cod: " + response.code(), 
                        Toast.LENGTH_LONG).show();
                }
            }

            @Override
            public void onFailure(Call<okhttp3.ResponseBody> call, Throwable t) {
                loadingDialog.dismiss();
                Log.e("DocumentDownload", "Download failed", t);
                Toast.makeText(IstoricMedicalActivity.this, 
                    "Eroare la descărcarea documentului: " + t.getMessage(), 
                    Toast.LENGTH_LONG).show();
            }
        });
    }

    @Override
    public void onDeleteDocument(Document document) {
        new AlertDialog.Builder(this)
            .setTitle("Șterge Document")
            .setMessage("Sigur doriți să ștergeți documentul \"" + document.getTitle() + "\"?")
            .setPositiveButton("Da", (dialog, which) -> deleteDocument(document))
            .setNegativeButton("Nu", null)
            .show();
    }

    private void deleteDocument(Document document) {
        ApiService apiService = ApiClient.getClient().create(ApiService.class);
        
        // Create JSON body with document_id and patient_id
        JSONObject jsonBody = new JSONObject();
        try {
            jsonBody.put("document_id", document.getId());
            jsonBody.put("patient_id", patientId);
        } catch (JSONException e) {
            Log.e("IstoricMedical", "Error creating JSON body for delete", e);
            Toast.makeText(IstoricMedicalActivity.this, "Eroare la pregătirea cererii de ștergere", Toast.LENGTH_LONG).show();
            return;
        }
        
        RequestBody body = RequestBody.create(okhttp3.MediaType.parse("application/json"), jsonBody.toString());
        
        apiService.deleteDocument(body).enqueue(new Callback<DocumentDeleteResponse>() {
            @Override
            public void onResponse(Call<DocumentDeleteResponse> call, Response<DocumentDeleteResponse> response) {
                if (response.isSuccessful() && response.body() != null) {
                    DocumentDeleteResponse deleteResponse = response.body();
                    if (deleteResponse.isSuccess()) {
                        Toast.makeText(IstoricMedicalActivity.this, "Document șters cu succes!", Toast.LENGTH_LONG).show();
                        loadDocuments();
                    } else {
                        Toast.makeText(IstoricMedicalActivity.this, 
                            "Eroare la ștergerea documentului: " + deleteResponse.getMessage(), 
                            Toast.LENGTH_LONG).show();
                    }
                } else {
                    Toast.makeText(IstoricMedicalActivity.this, "Eroare la ștergerea documentului.", Toast.LENGTH_LONG).show();
                }
            }

            @Override
            public void onFailure(Call<DocumentDeleteResponse> call, Throwable t) {
                Toast.makeText(IstoricMedicalActivity.this, "Eroare la ștergerea documentului: " + t.getMessage(), Toast.LENGTH_LONG).show();
            }
        });
    }

    private File createTempFileFromUri(Uri uri) throws IOException {
        Log.d("DocumentAnalysis", "Creating temp file from URI: " + uri.toString());
        
        InputStream inputStream = null;
        FileOutputStream outputStream = null;
        File tempFile = null;
        
        try {
            inputStream = getContentResolver().openInputStream(uri);
            if (inputStream == null) {
                throw new IOException("Could not open input stream for URI: " + uri);
            }
            
            String extension = getFileExtension(uri);
            Log.d("DocumentAnalysis", "File extension: " + extension);
            
            tempFile = File.createTempFile("document", extension);
            Log.d("DocumentAnalysis", "Temp file created: " + tempFile.getAbsolutePath());
            
            outputStream = new FileOutputStream(tempFile);
            
            byte[] buffer = new byte[4096];
            int bytesRead;
            int totalBytes = 0;
            while ((bytesRead = inputStream.read(buffer)) != -1) {
                outputStream.write(buffer, 0, bytesRead);
                totalBytes += bytesRead;
            }
            
            Log.d("DocumentAnalysis", "Total bytes written: " + totalBytes);
            
            // Ensure all data is written
            outputStream.flush();
            
            Log.d("DocumentAnalysis", "Final temp file size: " + tempFile.length());
            
            if (tempFile.length() == 0) {
                throw new IOException("Created temp file is empty");
            }
            
            return tempFile;
            
        } catch (IllegalStateException e) {
            Log.e("DocumentAnalysis", "IllegalStateException while creating temp file", e);
            throw new IOException("Eroare de stare ilegală la procesarea fișierului: " + e.getMessage(), e);
        } catch (Exception e) {
            Log.e("DocumentAnalysis", "Exception while creating temp file", e);
            throw new IOException("Eroare la procesarea fișierului: " + e.getMessage(), e);
        } finally {
            // Clean up resources
            if (inputStream != null) {
                try {
                    inputStream.close();
                } catch (IOException e) {
                    Log.e("DocumentAnalysis", "Error closing input stream", e);
                }
            }
            if (outputStream != null) {
                try {
                    outputStream.close();
                } catch (IOException e) {
                    Log.e("DocumentAnalysis", "Error closing output stream", e);
                }
            }
        }
    }

    private File createTempFileFromData(byte[] data, String originalFileName) throws IOException {
        Log.d("DocumentAnalysis", "Creating temp file from data, size: " + data.length + ", original name: " + originalFileName);
        
        // Get file extension from original filename
        String extension = "";
        if (originalFileName != null && originalFileName.contains(".")) {
            extension = originalFileName.substring(originalFileName.lastIndexOf("."));
        }
        if (extension.isEmpty()) {
            extension = ".tmp";
        }
        
        // Create temp file
        File tempFile = File.createTempFile("analysis_doc_" + System.currentTimeMillis(), extension);
        Log.d("DocumentAnalysis", "Temp file created: " + tempFile.getAbsolutePath());
        
        // Write data to file
        FileOutputStream outputStream = new FileOutputStream(tempFile);
        try {
            outputStream.write(data);
            outputStream.flush();
            Log.d("DocumentAnalysis", "Data written to temp file, size: " + tempFile.length());
        } finally {
            outputStream.close();
        }
        
        return tempFile;
    }

    private File createFreshTempFileFromUri(Uri uri) throws IOException {
        Log.d("DocumentUpload", "Creating fresh temp file from URI: " + uri.toString());
        
        InputStream inputStream = null;
        FileOutputStream outputStream = null;
        File tempFile = null;
        
        try {
            // Try to get a fresh input stream
            inputStream = getContentResolver().openInputStream(uri);
            if (inputStream == null) {
                throw new IOException("Could not open input stream for URI: " + uri);
            }
            
            // Get file extension
            String extension = getFileExtension(uri);
            if (extension == null || extension.isEmpty()) {
                extension = ".tmp";
            }
            Log.d("DocumentUpload", "File extension: " + extension);
            
            // Create temp file with unique name
            tempFile = File.createTempFile("upload_doc_" + System.currentTimeMillis(), extension);
            Log.d("DocumentUpload", "Fresh temp file created: " + tempFile.getAbsolutePath());
            
            outputStream = new FileOutputStream(tempFile);
            
            // Copy file content
            byte[] buffer = new byte[8192]; // Larger buffer for better performance
            int bytesRead;
            int totalBytes = 0;
            while ((bytesRead = inputStream.read(buffer)) != -1) {
                outputStream.write(buffer, 0, bytesRead);
                totalBytes += bytesRead;
            }
            
            Log.d("DocumentUpload", "Total bytes written: " + totalBytes);
            
            // Ensure all data is written
            outputStream.flush();
            
            Log.d("DocumentUpload", "Final temp file size: " + tempFile.length());
            
            if (tempFile.length() == 0) {
                throw new IOException("Created temp file is empty");
            }
            
            return tempFile;
            
        } catch (IllegalStateException e) {
            Log.e("DocumentUpload", "IllegalStateException while creating fresh temp file", e);
            throw new IOException("Eroare de stare ilegală - fișierul a fost deja utilizat. Încercați să selectați din nou fișierul.", e);
        } catch (Exception e) {
            Log.e("DocumentUpload", "Exception while creating fresh temp file", e);
            throw new IOException("Eroare la procesarea fișierului: " + e.getMessage(), e);
        } finally {
            // Clean up resources
            if (inputStream != null) {
                try {
                    inputStream.close();
                } catch (IOException e) {
                    Log.e("DocumentUpload", "Error closing input stream", e);
                }
            }
            if (outputStream != null) {
                try {
                    outputStream.close();
                } catch (IOException e) {
                    Log.e("DocumentUpload", "Error closing output stream", e);
                }
            }
        }
    }

    private String getMimeType(Uri uri) {
        ContentResolver contentResolver = getContentResolver();
        return contentResolver.getType(uri);
    }

    private String getFileExtension(Uri uri) {
        String mimeType = getMimeType(uri);
        if (mimeType != null) {
            return MimeTypeMap.getSingleton().getExtensionFromMimeType(mimeType);
        }
        return "";
    }
} 
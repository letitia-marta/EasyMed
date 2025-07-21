package com.example.easymed_mobile;

import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.FrameLayout;
import android.widget.LinearLayout;
import android.widget.ProgressBar;
import android.widget.Spinner;
import android.widget.TextView;
import android.widget.Toast;
import androidx.annotation.Nullable;
import androidx.appcompat.app.AppCompatActivity;
import androidx.recyclerview.widget.RecyclerView;
import android.app.Dialog;
import android.text.Editable;
import android.text.TextWatcher;
import android.view.LayoutInflater;
import android.view.Window;
import android.widget.EditText;
import android.widget.HorizontalScrollView;
import android.widget.Button;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;
import java.util.ArrayList;
import java.util.List;
import android.widget.GridLayout;
import java.util.Calendar;
import java.util.Locale;
import java.text.SimpleDateFormat;
import android.widget.LinearLayout;
import java.util.Date;
import android.widget.ArrayAdapter;
import android.widget.AdapterView;
import retrofit2.Call;
import android.util.Log;
import androidx.recyclerview.widget.GridLayoutManager;
import okhttp3.MediaType;
import okhttp3.RequestBody;
import okhttp3.MultipartBody;
import retrofit2.Response;
import com.example.easymed_mobile.AvailableTimeSlot;
import com.example.easymed_mobile.AppointmentSlot;
import android.view.WindowManager;
import android.graphics.Color;
import android.view.ViewGroup;

/**
 * Activitate pentru programarea unei consultații medicale în EasyMed
 *
 * Această activitate permite:
 * - Selectarea medicului și specializării
 * - Alegerea datei și orei pentru consultație
 * - Vizualizarea și filtrarea medicilor disponibili
 * - Selectarea tipului de consultație
 * - Salvarea programării în sistem
 *
 * <p>Folosește API-ul EasyMed pentru a încărca lista medicilor, specializărilor,
 * sloturilor disponibile și pentru a salva programarea.</p>
 *
 * @author EasyMed Mobile Team
 * @version 1.0
 * @since 2024
 */
public class ProgramareActivity extends AppCompatActivity {
    // UI Components
    private TextView backButton;
    private Button selectDoctorButton;
    private TextView selectedDoctorPreview;
    private FrameLayout calendarContainer;
    private Spinner consultationTypeSpinner;
    private RecyclerView timeSlotsRecyclerView;
    private Button saveAppointmentButton;
    private ProgressBar loadingIndicator;

    // Profile dropdown
    private LinearLayout dropdownMenu;
    private TextView profileIcon, profileOption, logoutOption;

    // Doctor data
    private List<Doctor> allDoctors = new ArrayList<>();
    private List<String> specialties = new ArrayList<>();
    private Doctor selectedDoctor = null;
    private String selectedSpecialty = "all";

    // Calendar data
    private Calendar currentDate;
    private TextView calendarMonthYear;
    private Button prevMonthButton, nextMonthButton;
    private GridLayout calendarGrid;
    private String selectedDate = null;

    // Time slots data
    private String selectedTime = null;
    private String selectedConsultationType = null;
    private List<Appointment> existingAppointments = new ArrayList<>();
    private List<AvailableTimeSlot> availableTimeSlots = new ArrayList<>();

    /**
     * Metodă apelată la crearea activității
     *
     * Inițializează interfața, configurează calendarul, spinner-ul de consultații și butoanele principale.
     *
     * @param savedInstanceState Starea salvată a activității (poate fi null)
     */
    @Override
    protected void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_programare);

        initializeViews();
        setupProfileDropdown();
        setupListeners();
        setupCalendar();
        setupConsultationTypeSpinner();
        setupSaveButton();
    }

    private void initializeViews() {
        backButton = findViewById(R.id.back_button);
        selectDoctorButton = findViewById(R.id.select_doctor_button);
        selectedDoctorPreview = findViewById(R.id.selected_doctor_preview);
        calendarContainer = findViewById(R.id.calendar_container);
        consultationTypeSpinner = findViewById(R.id.consultation_type_spinner);
        timeSlotsRecyclerView = findViewById(R.id.time_slots_recycler_view);
        saveAppointmentButton = findViewById(R.id.save_appointment_button);
        loadingIndicator = findViewById(R.id.loading_indicator);

        // Profile dropdown
        profileIcon = findViewById(R.id.profile_icon);
        profileIcon.setOnClickListener(v -> {
            Intent intent = new Intent(this, ProfileActivity.class);
            startActivity(intent);
        });
    }

    private void showCalendarPlaceholder() {
        TextView placeholder = new TextView(this);
        placeholder.setText("[Calendar will be displayed here]");
        placeholder.setTextColor(getResources().getColor(android.R.color.white));
        placeholder.setTextSize(18);
        placeholder.setPadding(32, 32, 32, 32);
        calendarContainer.removeAllViews();
        calendarContainer.addView(placeholder);
    }

    private void setupProfileDropdown() {
        // The dropdown menu and its options are removed as per the edit hint.
        // The profileIcon now directly navigates to ProfileActivity.
    }

    private void setupListeners() {
        backButton.setOnClickListener(v -> finish());
        selectDoctorButton.setOnClickListener(v -> showDoctorSelectorDialog());
    }

    private void showDoctorSelectorDialog() {
        Dialog dialog = new Dialog(this);
        dialog.requestWindowFeature(Window.FEATURE_NO_TITLE);
        dialog.setContentView(R.layout.dialog_select_doctor);

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
        EditText searchInput = dialog.findViewById(R.id.search_doctor_input);
        LinearLayout specialtyFilterContainer = dialog.findViewById(R.id.specialty_filter_container);
        RecyclerView doctorListRecyclerView = dialog.findViewById(R.id.doctor_list_recycler_view);
        TextView selectedDoctorPreview = dialog.findViewById(R.id.selected_doctor_preview);
        Button closeDialogButton = dialog.findViewById(R.id.close_dialog_button);
        Button confirmDoctorButton = dialog.findViewById(R.id.confirm_doctor_button);

        // Fetch doctors and specialties if not already loaded
        if (allDoctors.isEmpty() || specialties.isEmpty()) {
            fetchDoctorsAndSpecialties(() -> setupDoctorDialogUI(dialog, specialtyFilterContainer, doctorListRecyclerView, searchInput, selectedDoctorPreview));
        } else {
            setupDoctorDialogUI(dialog, specialtyFilterContainer, doctorListRecyclerView, searchInput, selectedDoctorPreview);
        }

        closeDialogButton.setOnClickListener(v -> dialog.dismiss());
        confirmDoctorButton.setOnClickListener(v -> {
            if (selectedDoctor != null) {
                selectDoctorButton.setText("Dr. " + selectedDoctor.getFullName() + " - " + selectedDoctor.getSpecializare());
                selectedDoctorPreview.setText("Selectat: Dr. " + selectedDoctor.getFullName() + " - " + selectedDoctor.getSpecializare());
            }
            dialog.dismiss();
        });

        dialog.show();
    }

    private void fetchDoctorsAndSpecialties(Runnable onLoaded) {
        loadingIndicator.setVisibility(View.VISIBLE);
        ApiService apiService = ApiClient.getClient().create(ApiService.class);
        // Fetch doctors
        apiService.getDoctors(null, null, false, "nume", "ASC").enqueue(new retrofit2.Callback<DoctorsResponse>() {
            @Override
            public void onResponse(retrofit2.Call<DoctorsResponse> call, retrofit2.Response<DoctorsResponse> response) {
                if (response.isSuccessful() && response.body() != null && response.body().isSuccess()) {
                    allDoctors = response.body().getDoctors();
                    // Fetch specialties
                    apiService.getSpecialties().enqueue(new retrofit2.Callback<List<String>>() {
                        @Override
                        public void onResponse(retrofit2.Call<List<String>> call2, retrofit2.Response<List<String>> response2) {
                            loadingIndicator.setVisibility(View.GONE);
                            if (response2.isSuccessful() && response2.body() != null) {
                                specialties = response2.body();
                                onLoaded.run();
                            }
                        }
                        @Override
                        public void onFailure(retrofit2.Call<List<String>> call2, Throwable t2) {
                            loadingIndicator.setVisibility(View.GONE);
                        }
                    });
                } else {
                    loadingIndicator.setVisibility(View.GONE);
                }
            }
            @Override
            public void onFailure(retrofit2.Call<DoctorsResponse> call, Throwable t) {
                loadingIndicator.setVisibility(View.GONE);
            }
        });
    }

    private void setupDoctorDialogUI(Dialog dialog, LinearLayout specialtyFilterContainer, RecyclerView doctorListRecyclerView, EditText searchInput, TextView selectedDoctorPreview) {
        // Setup specialty filter buttons
        specialtyFilterContainer.removeAllViews();
        addSpecialtyButton(specialtyFilterContainer, "Toate", "all", true);
        for (String spec : specialties) {
            addSpecialtyButton(specialtyFilterContainer, spec, spec, false);
        }
        // Setup doctor list
        List<Doctor> filteredDoctors = new ArrayList<>(allDoctors);
        DoctorDialogAdapter adapter = new DoctorDialogAdapter(filteredDoctors, doctor -> {
            selectedDoctor = doctor;
            selectedDoctorPreview.setText("Selectat: Dr. " + doctor.getFullName() + " - " + doctor.getSpecializare());
        });
        doctorListRecyclerView.setLayoutManager(new LinearLayoutManager(this));
        doctorListRecyclerView.setAdapter(adapter);
        // Specialty filter logic
        for (int i = 0; i < specialtyFilterContainer.getChildCount(); i++) {
            View btn = specialtyFilterContainer.getChildAt(i);
            btn.setOnClickListener(v -> {
                for (int j = 0; j < specialtyFilterContainer.getChildCount(); j++) {
                    specialtyFilterContainer.getChildAt(j).setBackgroundColor(getResources().getColor(j == 0 ? R.color.easymed_accent : R.color.easymed_secondary));
                }
                btn.setBackgroundColor(getResources().getColor(R.color.easymed_accent));
                selectedSpecialty = (String) btn.getTag();
                filterDoctors(adapter, searchInput.getText().toString());
            });
        }
        // Search logic
        searchInput.addTextChangedListener(new TextWatcher() {
            @Override public void beforeTextChanged(CharSequence s, int start, int count, int after) {}
            @Override public void onTextChanged(CharSequence s, int start, int before, int count) {}
            @Override public void afterTextChanged(Editable s) {
                filterDoctors(adapter, s.toString());
            }
        });
    }

    private void addSpecialtyButton(LinearLayout container, String label, String tag, boolean isActive) {
        Button btn = new Button(this);
        btn.setText(label);
        btn.setTag(tag);
        btn.setTextColor(isActive ? getResources().getColor(android.R.color.black) : getResources().getColor(android.R.color.white));
        btn.setBackgroundColor(isActive ? getResources().getColor(R.color.easymed_accent) : getResources().getColor(R.color.easymed_secondary));
        btn.setPadding(24, 12, 24, 12);
        LinearLayout.LayoutParams params = new LinearLayout.LayoutParams(LinearLayout.LayoutParams.WRAP_CONTENT, LinearLayout.LayoutParams.WRAP_CONTENT);
        params.setMargins(8, 0, 8, 0);
        btn.setLayoutParams(params);
        container.addView(btn);
    }

    private void filterDoctors(DoctorDialogAdapter adapter, String query) {
        List<Doctor> filtered = new ArrayList<>();
        for (Doctor d : allDoctors) {
            boolean matchesSpecialty = selectedSpecialty.equals("all") || d.getSpecializare().equals(selectedSpecialty);
            boolean matchesQuery = (d.getNume() + " " + d.getPrenume() + " " + d.getSpecializare()).toLowerCase().contains(query.toLowerCase());
            if (matchesSpecialty && matchesQuery) {
                filtered.add(d);
            }
        }
        adapter.updateDoctors(filtered);
    }

    private void setupCalendar() {
        // Inflate calendar view
        View calendarView = LayoutInflater.from(this).inflate(R.layout.calendar_view, calendarContainer, false);
        calendarContainer.removeAllViews();
        calendarContainer.addView(calendarView);

        // Initialize calendar components
        calendarMonthYear = calendarView.findViewById(R.id.calendar_month_year);
        prevMonthButton = calendarView.findViewById(R.id.prev_month_button);
        nextMonthButton = calendarView.findViewById(R.id.next_month_button);
        calendarGrid = calendarView.findViewById(R.id.calendar_grid);

        // Initialize current date
        currentDate = Calendar.getInstance();
        
        // Setup calendar navigation
        prevMonthButton.setOnClickListener(v -> {
            currentDate.add(Calendar.MONTH, -1);
            updateCalendar();
        });
        
        nextMonthButton.setOnClickListener(v -> {
            currentDate.add(Calendar.MONTH, 1);
            updateCalendar();
        });

        updateCalendar();
    }

    private void updateCalendar() {
        // Update month/year display
        SimpleDateFormat monthFormat = new SimpleDateFormat("MMMM yyyy", new Locale("ro"));
        calendarMonthYear.setText(monthFormat.format(currentDate.getTime()));

        // Clear previous calendar days
        calendarGrid.removeAllViews();

        // Get calendar data
        Calendar firstDay = (Calendar) currentDate.clone();
        firstDay.set(Calendar.DAY_OF_MONTH, 1);
        
        int firstDayOfWeek = firstDay.get(Calendar.DAY_OF_WEEK);
        // Convert to Monday=0 format
        int startOffset = (firstDayOfWeek + 5) % 7;
        
        int daysInMonth = currentDate.getActualMaximum(Calendar.DAY_OF_MONTH);
        Calendar today = Calendar.getInstance();

        // Add empty cells for days before the first day of the month
        for (int i = 0; i < startOffset; i++) {
            addCalendarCell("", false, false, null);
        }

        // Add days of the month
        for (int day = 1; day <= daysInMonth; day++) {
            Calendar dayCalendar = (Calendar) currentDate.clone();
            dayCalendar.set(Calendar.DAY_OF_MONTH, day);
            
            boolean isPast = dayCalendar.before(today);
            boolean isToday = dayCalendar.get(Calendar.YEAR) == today.get(Calendar.YEAR) &&
                             dayCalendar.get(Calendar.MONTH) == today.get(Calendar.MONTH) &&
                             dayCalendar.get(Calendar.DAY_OF_MONTH) == today.get(Calendar.DAY_OF_MONTH);
            
            addCalendarCell(String.valueOf(day), !isPast, isToday, (Calendar) dayCalendar.clone());
        }
    }

    private void addCalendarCell(String text, boolean isClickable, boolean isToday, Calendar cellDate) {
        TextView dayView = new TextView(this);
        dayView.setText(text);
        dayView.setTextColor(getResources().getColor(android.R.color.white));
        dayView.setTextSize(16);
        dayView.setGravity(android.view.Gravity.CENTER);
        dayView.setPadding(8, 8, 8, 8);

        // Set background based on state
        if (isToday) {
            dayView.setBackgroundColor(getResources().getColor(R.color.easymed_accent));
            dayView.setTextColor(getResources().getColor(android.R.color.black));
            dayView.setClickable(true);
            dayView.setFocusable(true);
            if (cellDate != null) {
                dayView.setTag(cellDate);
            }
            dayView.setOnClickListener(v -> {
                if (selectedDoctor == null) {
                    Toast.makeText(this, "Vă rugăm să selectați mai întâi un medic.", Toast.LENGTH_SHORT).show();
                    return;
                }
                try {
                    Calendar clickedDate = (Calendar) v.getTag();
                    SimpleDateFormat dateFormat = new SimpleDateFormat("yyyy-MM-dd", Locale.US);
                    selectedDate = dateFormat.format(clickedDate.getTime());
                    Log.d("ProgramareActivity", "Selected date: " + selectedDate);

                    updateCalendarCellStyles();
                    dayView.setBackgroundColor(getResources().getColor(R.color.easymed_accent));
                    dayView.setTextColor(getResources().getColor(android.R.color.black));
                    // Load time slots for selected date
                    loadTimeSlots();
                } catch (Exception e) {
                    Log.e("ProgramareActivity", "Error selecting date: " + e.getMessage(), e);
                    Toast.makeText(this, "Eroare la selectarea datei.", Toast.LENGTH_SHORT).show();
                }
            });
        } else if (isClickable) {
            dayView.setBackgroundColor(getResources().getColor(R.color.easymed_secondary));
            dayView.setClickable(true);
            dayView.setFocusable(true);
            if (cellDate != null) {
                dayView.setTag(cellDate);
            }
            // Add click listener
            dayView.setOnClickListener(v -> {
                if (selectedDoctor == null) {
                    Toast.makeText(this, "Vă rugăm să selectați mai întâi un medic.", Toast.LENGTH_SHORT).show();
                    return;
                }
                try {
                    Calendar clickedDate = (Calendar) v.getTag();
                    SimpleDateFormat dateFormat = new SimpleDateFormat("yyyy-MM-dd", Locale.US);
                    selectedDate = dateFormat.format(clickedDate.getTime());
                    Log.d("ProgramareActivity", "Selected date: " + selectedDate);
                    // Update visual selection
                    updateCalendarCellStyles();
                    dayView.setBackgroundColor(getResources().getColor(R.color.easymed_accent));
                    dayView.setTextColor(getResources().getColor(android.R.color.black));
                    // Load time slots for selected date
                    loadTimeSlots();
                } catch (Exception e) {
                    Log.e("ProgramareActivity", "Error selecting date: " + e.getMessage(), e);
                    Toast.makeText(this, "Eroare la selectarea datei.", Toast.LENGTH_SHORT).show();
                }
            });
        } else if (cellDate != null && text != null && !text.isEmpty()) {
            // Past day: gray background, black text
            dayView.setBackgroundColor(getResources().getColor(android.R.color.darker_gray));
            dayView.setTextColor(getResources().getColor(android.R.color.black));
        } else if (text == null || text.isEmpty()) {
            // Empty cell: gray background, black text
            dayView.setBackgroundColor(getResources().getColor(android.R.color.darker_gray));
            dayView.setTextColor(getResources().getColor(android.R.color.black));
        }
        
        // Always set the tag for day cells (not empty)
        if (cellDate != null && text != null && !text.isEmpty()) {
            dayView.setTag(cellDate);
        }
        
        // Add to grid
        GridLayout.LayoutParams params = new GridLayout.LayoutParams();
        params.width = 0;
        params.height = GridLayout.LayoutParams.WRAP_CONTENT;
        params.columnSpec = GridLayout.spec(GridLayout.UNDEFINED, 1f);
        dayView.setLayoutParams(params);
        
        calendarGrid.addView(dayView);
    }

    // Helper to update calendar cell styles
    private void updateCalendarCellStyles() {
        for (int i = 0; i < calendarGrid.getChildCount(); i++) {
            View child = calendarGrid.getChildAt(i);
            if (child instanceof TextView) {
                TextView tv = (TextView) child;
                CharSequence dayText = tv.getText();
                if (dayText == null || dayText.length() == 0) continue;
                Calendar cellDateTag = (Calendar) tv.getTag();
                boolean isPast = false;
                if (cellDateTag != null) {
                    Calendar today = Calendar.getInstance();
                    today.set(Calendar.HOUR_OF_DAY, 0);
                    today.set(Calendar.MINUTE, 0);
                    today.set(Calendar.SECOND, 0);
                    today.set(Calendar.MILLISECOND, 0);
                    Calendar cellDateOnly = (Calendar) cellDateTag.clone();
                    cellDateOnly.set(Calendar.HOUR_OF_DAY, 0);
                    cellDateOnly.set(Calendar.MINUTE, 0);
                    cellDateOnly.set(Calendar.SECOND, 0);
                    cellDateOnly.set(Calendar.MILLISECOND, 0);
                    isPast = cellDateOnly.before(today);
                }
                if (isPast) {
                    tv.setBackgroundColor(getResources().getColor(android.R.color.darker_gray));
                    tv.setTextColor(getResources().getColor(android.R.color.black));
                } else {
                    tv.setBackgroundColor(getResources().getColor(R.color.easymed_secondary));
                    tv.setTextColor(getResources().getColor(android.R.color.white));
                }
            }
        }
    }

    private void loadTimeSlots() {
        if (selectedDate == null || selectedDoctor == null) {
            Log.e("ProgramareActivity", "selectedDate or selectedDoctor is null. selectedDate=" + selectedDate + ", selectedDoctor=" + selectedDoctor);
            return;
        }
        Log.d("ProgramareActivity", "Calling getAppointmentsForDoctorAndDate with doctorId=" + selectedDoctor.getId() + ", date=" + selectedDate);
        loadingIndicator.setVisibility(View.VISIBLE);
        ApiService apiService = ApiClient.getClient().create(ApiService.class);
        apiService.getAppointmentsForDoctorAndDate(selectedDate, selectedDoctor.getId())
            .enqueue(new retrofit2.Callback<List<AppointmentSlot>>() {
                @Override
                public void onResponse(retrofit2.Call<List<AppointmentSlot>> call, retrofit2.Response<List<AppointmentSlot>> response) {
                    loadingIndicator.setVisibility(View.GONE);
                    Log.d("ProgramareActivity", "Response code: " + response.code());
                    try {
                        if (response.errorBody() != null) {
                            Log.d("ProgramareActivity", "Error body: " + response.errorBody().string());
                        }
                    } catch (Exception e) {
                        Log.e("ProgramareActivity", "Error reading errorBody", e);
                    }
                    if (response.body() != null) {
                        Log.d("ProgramareActivity", "Body: " + new com.google.gson.Gson().toJson(response.body()));
                    }
                    if (response.isSuccessful() && response.body() != null) {
                        List<AppointmentSlot> appointments = response.body();
                        Log.d("ProgramareActivity", "Received " + appointments.size() + " appointments");
                        for (AppointmentSlot apt : appointments) {
                            Log.d("ProgramareActivity", "Appointment: id=" + apt.id + ", time_slot=" + apt.getTimeSlot());
                        }
                        generateAndDisplayTimeSlots(appointments);
                    } else {
                        Log.e("ProgramareActivity", "API response not successful or empty for appointments");
                        Toast.makeText(ProgramareActivity.this, "API response not successful or empty for appointments", Toast.LENGTH_SHORT).show();
                        generateAndDisplayTimeSlots(new ArrayList<>());
                    }
                }
                @Override
                public void onFailure(retrofit2.Call<List<AppointmentSlot>> call, Throwable t) {
                    loadingIndicator.setVisibility(View.GONE);
                    Log.e("ProgramareActivity", "Error fetching appointments: " + t.getMessage(), t);
                    Toast.makeText(ProgramareActivity.this, "API call failed: " + t.getMessage(), Toast.LENGTH_SHORT).show();
                    generateAndDisplayTimeSlots(new ArrayList<>());
                }
            });
    }

    private void generateAndDisplayTimeSlots(List<AppointmentSlot> appointments) {
        List<AvailableTimeSlot> slots = new ArrayList<>();
        java.util.Calendar now = java.util.Calendar.getInstance();
        String todayStr = new java.text.SimpleDateFormat("yyyy-MM-dd").format(now.getTime());
        boolean isToday = selectedDate.equals(todayStr);
        for (int hour = 8; hour <= 16; hour++) {
            for (int minute : new int[]{0, 30}) {
                String time = String.format("%02d:%02d", hour, minute);
                java.util.Calendar slotCal = java.util.Calendar.getInstance();
                try {
                    java.text.SimpleDateFormat sdf = new java.text.SimpleDateFormat("yyyy-MM-dd HH:mm");
                    slotCal.setTime(sdf.parse(selectedDate + " " + time));
                } catch (Exception e) {
                    e.printStackTrace();
                }
                boolean isPast = false;
                if (selectedDate.compareTo(todayStr) < 0) {
                    isPast = true;
                } else if (isToday && slotCal.getTime().before(now.getTime())) {
                    isPast = true;
                }
                boolean isOccupied = false;
                for (AppointmentSlot apt : appointments) {
                    String aptTime = apt.getTimeSlot();
                    Log.d("ProgramareActivity", "Raw appointment time: " + aptTime);
                    if (aptTime != null && aptTime.length() >= 5) {
                        aptTime = aptTime.substring(0, 5); // get only HH:mm
                    }
                    Log.d("ProgramareActivity", "Comparing slot " + time + " with appointment " + aptTime);
                    if (aptTime != null && aptTime.equals(time)) {
                        Log.d("ProgramareActivity", "Slot " + time + " is OCCUPIED!");
                        isOccupied = true;
                        break;
                    }
                }
                boolean available = !isPast && !isOccupied;
                AvailableTimeSlot slot = new AvailableTimeSlot();
                // Set fields via reflection or constructor
                try {
                    java.lang.reflect.Field timeF = AvailableTimeSlot.class.getDeclaredField("time");
                    timeF.setAccessible(true);
                    timeF.set(slot, time);
                    java.lang.reflect.Field availF = AvailableTimeSlot.class.getDeclaredField("available");
                    availF.setAccessible(true);
                    availF.set(slot, available);
                    java.lang.reflect.Field pastF = AvailableTimeSlot.class.getDeclaredField("isPast");
                    pastF.setAccessible(true);
                    pastF.set(slot, isPast);
                    java.lang.reflect.Field occF = AvailableTimeSlot.class.getDeclaredField("isOccupied");
                    occF.setAccessible(true);
                    occF.set(slot, isOccupied);
                } catch (Exception e) {
                    e.printStackTrace();
                }
                slots.add(slot);
            }
        }
        availableTimeSlots = slots;
        displayAvailableTimeSlots();
    }

    private void displayAvailableTimeSlots() {
        timeSlotsRecyclerView.setVisibility(View.VISIBLE);
        TimeSlotAdapter adapter = new TimeSlotAdapter(availableTimeSlots, selectedTime, timeSlot -> {
            selectedTime = timeSlot;
            updateSaveButtonVisibility();
        });
        int columns = 4;
        timeSlotsRecyclerView.setLayoutManager(new GridLayoutManager(this, columns));
        timeSlotsRecyclerView.setAdapter(adapter);
    }

    private void updateSaveButtonVisibility() {
        if (selectedDoctor != null && selectedDate != null && selectedTime != null && selectedConsultationType != null) {
            saveAppointmentButton.setVisibility(View.VISIBLE);
        } else {
            saveAppointmentButton.setVisibility(View.GONE);
        }
    }

    private void setupConsultationTypeSpinner() {
        String[] consultationTypes = {
            "Selectează motivul consultației",
            "Consult",
            "Vaccinare", 
            "Monitorizare",
            "Examen bilanț",
            "Prescriere rețetă cronică"
        };
        
        ArrayAdapter<String> adapter = new ArrayAdapter<String>(this, 
            android.R.layout.simple_spinner_item, consultationTypes) {
            @Override
            public View getView(int position, View convertView, ViewGroup parent) {
                View view = super.getView(position, convertView, parent);
                if (view instanceof TextView) {
                    ((TextView) view).setTextColor(Color.WHITE);
                }
                return view;
            }

            @Override
            public View getDropDownView(int position, View convertView, ViewGroup parent) {
                View view = super.getDropDownView(position, convertView, parent);
                if (view instanceof TextView) {
                    ((TextView) view).setTextColor(Color.WHITE);
                    view.setBackgroundColor(Color.parseColor("#13181D"));
                }
                return view;
            }
        };
        adapter.setDropDownViewResource(android.R.layout.simple_spinner_dropdown_item);
        consultationTypeSpinner.setAdapter(adapter);
        
        consultationTypeSpinner.setOnItemSelectedListener(new AdapterView.OnItemSelectedListener() {
            @Override
            public void onItemSelected(AdapterView<?> parent, View view, int position, long id) {
                if (position > 0) {
                    selectedConsultationType = consultationTypes[position];
                } else {
                    selectedConsultationType = null;
                }
                updateSaveButtonVisibility();
            }

            @Override
            public void onNothingSelected(AdapterView<?> parent) {
                selectedConsultationType = null;
                updateSaveButtonVisibility();
            }
        });
    }

    private void setupSaveButton() {
        saveAppointmentButton.setOnClickListener(v -> {
            if (selectedDoctor == null || selectedDate == null || selectedTime == null || selectedConsultationType == null) {
                Toast.makeText(this, "Completați toate câmpurile!", Toast.LENGTH_SHORT).show();
                return;
            }
            // For demo, use a placeholder patient_id (e.g., 1)
            int patientId = 1;
            saveAppointment(patientId, selectedDoctor.getId(), selectedDate, selectedTime, selectedConsultationType);
        });
    }

    private void saveAppointment(int patientId, int doctorId, String date, String time, String consultationType) {
        loadingIndicator.setVisibility(View.VISIBLE);
        ApiService apiService = ApiClient.getClient().create(ApiService.class);
        // Use MultipartBody for form data
        RequestBody patientIdBody = RequestBody.create(MediaType.parse("text/plain"), String.valueOf(patientId));
        RequestBody doctorIdBody = RequestBody.create(MediaType.parse("text/plain"), String.valueOf(doctorId));
        RequestBody dateBody = RequestBody.create(MediaType.parse("text/plain"), date);
        RequestBody timeBody = RequestBody.create(MediaType.parse("text/plain"), time);
        RequestBody typeBody = RequestBody.create(MediaType.parse("text/plain"), consultationType);
        
        apiService.saveAppointment(patientIdBody, doctorIdBody, dateBody, timeBody, typeBody)
            .enqueue(new retrofit2.Callback<retrofit2.Response<Void>>() {
                @Override
                public void onResponse(retrofit2.Call<retrofit2.Response<Void>> call, retrofit2.Response<retrofit2.Response<Void>> response) {
                    loadingIndicator.setVisibility(View.GONE);
                    if (response.isSuccessful()) {
                        Toast.makeText(ProgramareActivity.this, "Programare salvată cu succes!", Toast.LENGTH_LONG).show();
                        // Optionally, reset fields or finish activity
                    } else {
                        Toast.makeText(ProgramareActivity.this, "Eroare la salvarea programării.", Toast.LENGTH_LONG).show();
                    }
                }
                @Override
                public void onFailure(retrofit2.Call<retrofit2.Response<Void>> call, Throwable t) {
                    loadingIndicator.setVisibility(View.GONE);
                    Toast.makeText(ProgramareActivity.this, "Eroare de rețea la salvarea programării.", Toast.LENGTH_LONG).show();
                }
            });
    }
} 
package com.example.easymed_mobile;

import retrofit2.Call;
import retrofit2.http.GET;
import retrofit2.http.Query;
import retrofit2.http.POST;
import retrofit2.http.Field;
import retrofit2.http.FormUrlEncoded;
import java.util.List;
import okhttp3.RequestBody;
import okhttp3.MultipartBody;
import retrofit2.http.Multipart;
import retrofit2.http.Part;
import com.example.easymed_mobile.AvailableTimeSlot;
import com.example.easymed_mobile.AppointmentSlot;
import com.example.easymed_mobile.Consultation;
import com.example.easymed_mobile.Appointment;

/**
 * Interfață pentru definirea endpoint-urilor API în aplicația EasyMed
 * 
 * Această interfață definește toate endpoint-urile disponibile pentru comunicarea
 * cu serverul EasyMed prin Retrofit:
 * - Operații cu utilizatori și autentificare
 * - Gestionarea medicilor și specializărilor
 * - Operații cu programări și consultații
 * - Gestionarea relațiilor medic-pacient
 * - Operații cu documente medicale
 * - Funcționalități de analiză AI
 */
public interface ApiService {
    /**
     * Returnează lista tuturor utilizatorilor
     * @return Lista de utilizatori
     */
    @GET("api/get_users.php")
    Call<List<User>> getUsers();
    
    /**
     * Returnează lista medicilor cu filtrare și sortare
     * @param search Termenul de căutare
     * @param specialty Specializarea pentru filtrare
     * @param myDoctors Dacă să returneze doar medicii mei
     * @param sort Criteriul de sortare
     * @param order Ordinea de sortare (asc/desc)
     * @return Răspunsul cu lista de medici
     */
    @GET("api/get_doctors.php")
    Call<DoctorsResponse> getDoctors(@Query("search") String search, 
                                   @Query("specialty") String specialty, 
                                   @Query("my_doctors") boolean myDoctors,
                                   @Query("sort") String sort,
                                   @Query("order") String order);
    
    /**
     * Returnează lista specializărilor medicale
     * @return Lista de specializări
     */
    @GET("api/get_specialties.php")
    Call<List<String>> getSpecialties();
    
    /**
     * Returnează programările pentru o dată și medic specific
     * @param date Data pentru care să se returneze programările
     * @param doctorId ID-ul medicului
     * @return Lista de programări
     */
    @GET("api/get_appointments.php")
    Call<List<Appointment>> getAppointments(@Query("date") String date, @Query("doctor_id") int doctorId);

    /**
     * Returnează sloturile de programare pentru un medic și dată
     * @param date Data pentru care să se returneze sloturile
     * @param doctorId ID-ul medicului
     * @return Lista de sloturi de programare
     */
    @GET("api/get_appointments.php")
    Call<List<AppointmentSlot>> getAppointmentsForDoctorAndDate(
        @Query("date") String date,
        @Query("doctor_id") int doctorId
    );

    /**
     * Salvează o nouă programare
     * @param patientId ID-ul pacientului
     * @param doctorId ID-ul medicului
     * @param date Data programării
     * @param time Ora programării
     * @param consultationType Tipul de consultație
     * @return Răspunsul operației
     */
    @Multipart
    @POST("api/save_appointment.php")
    Call<retrofit2.Response<Void>> saveAppointment(
        @Part("patient_id") RequestBody patientId,
        @Part("doctor_id") RequestBody doctorId,
        @Part("date") RequestBody date,
        @Part("time") RequestBody time,
        @Part("consultation_type") RequestBody consultationType
    );

    /**
     * Returnează consultațiile unui pacient
     * @param patientId ID-ul pacientului
     * @return Lista de consultații
     */
    @GET("api/get_consultations.php")
    Call<List<Consultation>> getConsultations(@Query("patient_id") int patientId);

    /**
     * Returnează programările viitoare ale unui pacient
     * @param patientId ID-ul pacientului
     * @return Lista de programări viitoare
     */
    @GET("api/get_appointments.php")
    Call<List<Appointment>> getUpcomingAppointments(@Query("patient_id") int patientId);

    /**
     * Returnează sloturile de timp disponibile pentru un medic și dată
     * @param medicId ID-ul medicului
     * @param date Data pentru care să se returneze sloturile
     * @return Lista de sloturi de timp disponibile
     */
    @GET("getAvailableTimeSlots.php")
    Call<List<AvailableTimeSlot>> getAvailableTimeSlots(@Query("medic_id") int medicId, @Query("date") String date);

    /**
     * Testează conexiunea cu serverul
     * @return Răspunsul testului de conexiune
     */
    @GET("api/test_connection.php")
    Call<TestConnectionResponse> testConnection();
    
    /**
     * Endpoint pentru debugging al răspunsurilor
     * @return Răspunsul de debug
     */
    @GET("api/debug_response.php")
    Call<TestConnectionResponse> debugResponse();

    /**
     * Actualizează o programare existentă
     * @param appointmentId ID-ul programării
     * @param pacientId ID-ul pacientului
     * @param medicId ID-ul medicului
     * @param dataProgramare Data programării
     * @param oraProgramare Ora programării
     * @param status Statusul programării
     * @param motivConsultatie Motivul consultației
     * @return Răspunsul operației de actualizare
     */
    @FormUrlEncoded
    @POST("api/updateAppointment.php")
    Call<UpdateAppointmentResponse> updateAppointment(
        @Field("appointmentId") int appointmentId,
        @Field("pacient_id") int pacientId,
        @Field("medic_id") int medicId,
        @Field("data_programare") String dataProgramare,
        @Field("ora_programare") String oraProgramare,
        @Field("status") String status,
        @Field("motiv_consultatie") String motivConsultatie
    );

    /**
     * Șterge o programare
     * @param appointmentId ID-ul programării de șters
     * @return Răspunsul operației de ștergere
     */
    @FormUrlEncoded
    @POST("api/deleteAppointment.php")
    Call<DeleteAppointmentResponse> deleteAppointment(
        @Field("appointmentId") int appointmentId
    );

    /**
     * Returnează detaliile unei consultații
     * @param consultationId ID-ul consultației
     * @return Detaliile consultației
     */
    @GET("api/get_consultation_details.php")
    Call<ConsultationDetailsResponse> getConsultationDetails(@Query("consultation_id") int consultationId);

    /**
     * Returnează tipurile de relații disponibile
     * @param patientId ID-ul pacientului
     * @return Lista de tipuri de relații
     */
    @GET("api/get_relationship_types.php")
    Call<List<RelationshipType>> getRelationshipTypes(@Query("patient_id") int patientId);

    /**
     * Returnează lista pacienților
     * @param currentPatientId ID-ul pacientului curent
     * @return Lista de pacienți
     */
    @GET("api/get_patients.php")
    Call<List<Patient>> getPatients(@Query("current_patient_id") int currentPatientId);

    /**
     * Returnează relațiile unui pacient
     * @param patientId ID-ul pacientului
     * @return Lista de relații
     */
    @GET("api/get_relationships.php")
    Call<List<Relationship>> getRelationships(@Query("patient_id") int patientId);

    /**
     * Adaugă o nouă relație
     * @param body Corpul cererii cu datele relației
     * @return Răspunsul operației
     */
    @POST("api/add_relationship.php")
    Call<RelationshipResponse> addRelationship(@retrofit2.http.Body okhttp3.RequestBody body);

    /**
     * Șterge o relație
     * @param body Corpul cererii cu ID-ul relației
     * @return Răspunsul operației
     */
    @POST("api/delete_relationship.php")
    Call<RelationshipResponse> deleteRelationship(@retrofit2.http.Body okhttp3.RequestBody body);

    /**
     * Returnează profilul unui pacient
     * @param patientId ID-ul pacientului
     * @return Profilul pacientului
     */
    @GET("api/get_patient_profile.php")
    Call<Patient> getPatientProfile(@Query("patient_id") int patientId);

    // Endpoint-uri pentru documente medicale
    /**
     * Returnează documentele unui pacient
     * @param patientId ID-ul pacientului
     * @return Lista de documente
     */
    @GET("api/get_documents.php")
    Call<List<Document>> getDocuments(@Query("patient_id") int patientId);

    /**
     * Încarcă un document nou
     * @param file Fișierul de încărcat
     * @param title Titlul documentului
     * @param type Tipul documentului
     * @param patientId ID-ul pacientului
     * @return Răspunsul operației de încărcare
     */
    @Multipart
    @POST("api/upload_document.php")
    Call<DocumentUploadResponse> uploadDocument(
        @Part MultipartBody.Part file,
        @Part("document_title") RequestBody title,
        @Part("document_type") RequestBody type,
        @Part("pacient_id") RequestBody patientId
    );

    /**
     * Analizează un document cu AI
     * @param file Fișierul de analizat
     * @return Răspunsul analizei AI
     */
    @Multipart
    @POST("api/test_simple_analysis.php")
    Call<DocumentAnalysisResponse> analyzeDocument(
        @Part MultipartBody.Part file
    );

    /**
     * Șterge un document
     * @param body Corpul cererii cu ID-ul documentului
     * @return Răspunsul operației de ștergere
     */
    @POST("api/delete_document.php")
    Call<DocumentDeleteResponse> deleteDocument(@retrofit2.http.Body okhttp3.RequestBody body);

    /**
     * Descarcă un document
     * @param documentId ID-ul documentului
     * @return Corpul răspunsului cu fișierul
     */
    @GET("api/download_document.php")
    Call<okhttp3.ResponseBody> downloadDocument(@Query("id") int documentId);
}
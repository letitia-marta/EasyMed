package com.example.easymed_mobile;

import com.google.gson.Gson;
import com.google.gson.GsonBuilder;
import retrofit2.Retrofit;
import retrofit2.converter.gson.GsonConverterFactory;
import okhttp3.OkHttpClient;
import okhttp3.Interceptor;
import okhttp3.Response;
import okhttp3.ResponseBody;
import java.io.IOException;
import java.util.concurrent.TimeUnit;
import android.util.Log;

/**
 * Clasă pentru configurarea și gestionarea clientului API în aplicația EasyMed
 * 
 * Această clasă folosește Retrofit pentru a configura conexiunea cu serverul:
 * - Configurează Retrofit cu URL-ul de bază
 * - Setează timeout-urile pentru conexiuni
 * - Adaugă interceptor pentru logging-ul răspunsurilor
 * - Configurează Gson pentru serializarea/deserializarea JSON
 * - Implementează pattern Singleton pentru instanța Retrofit
 */
public class ApiClient {
    private static Retrofit retrofit;

    /**
     * Returnează instanța Retrofit configurată pentru comunicarea cu API-ul
     * 
     * Această metodă implementează pattern-ul Singleton pentru a asigura
     * că există o singură instanță Retrofit în aplicație.
     * 
     * @return Instanța Retrofit configurată
     */
    public static Retrofit getClient() {
        if (retrofit == null) {
            // Creează o instanță Gson mai permisivă pentru parsing JSON
            Gson gson = new GsonBuilder()
                    .setLenient()
                    .create();
            
            // Creează interceptor pentru logging-ul răspunsurilor
            Interceptor responseInterceptor = new Interceptor() {
                @Override
                public Response intercept(Chain chain) throws IOException {
                    Response response = chain.proceed(chain.request());
                    
                    // Loghează cererea
                    Log.d("ApiClient", "Request: " + response.request().url());
                    Log.d("ApiClient", "Response Code: " + response.code());
                    
                    // Loghează corpul răspunsului
                    ResponseBody responseBody = response.body();
                    if (responseBody != null) {
                        String responseString = responseBody.string();
                        Log.d("ApiClient", "Raw Response Body: " + responseString);
                        Log.d("ApiClient", "Response Body Length: " + responseString.length());
                        
                        // Creează un nou corp de răspuns deoarece am consumat originalul
                        ResponseBody newResponseBody = ResponseBody.create(responseBody.contentType(), responseString);
                        response = response.newBuilder().body(newResponseBody).build();
                    }
                    
                    return response;
                }
            };
            
            // Creează OkHttpClient cu timeout-uri și interceptor
            OkHttpClient client = new OkHttpClient.Builder()
                    .connectTimeout(30, TimeUnit.SECONDS)  // Timeout pentru conexiune
                    .readTimeout(60, TimeUnit.SECONDS)     // Timeout pentru citire
                    .writeTimeout(60, TimeUnit.SECONDS)    // Timeout pentru scriere
                    .addInterceptor(responseInterceptor)   // Adaugă interceptor pentru logging
                    .build();
            
            // Configurează Retrofit cu toate setările
            retrofit = new Retrofit.Builder()
                    .baseUrl("http://10.0.2.2/EasyMed/")  // URL-ul de bază pentru API
                    .client(client)                        // Client-ul HTTP configurat
                    .addConverterFactory(GsonConverterFactory.create(gson))  // Converter pentru JSON
                    .build();
        }
        return retrofit;
    }
}

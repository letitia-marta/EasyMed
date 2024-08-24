package clase;

import java.util.ArrayList;
import java.util.List;

/**
 * Aceasta clasa defineste un obiect de tipul consultatie, cu caracteristicile sale:
 * ID generat si incrementat automat, pacient, data consultatiei, simptomele, diagnosticul, bilete de trimitere si retete
 * 
 * Metode:
 * public Consultatie() - constructor fara parametri
 * public Consultatie (Pacient, Data, List <String>, String, String, String, String) - constructor cu parametri
 * public void reset() - aduce la valoarea initiala ID-urile
 * getPacient() - returneaza pacientul
 * toText() - metoda de obtinere a detaliilor despre consultatie, pentru a fi stocate
 * toString() - metoda de obtinere detaliata a unei consultatii
 */
public class Consultatie
{
	final int IDconsultatie;
	Pacient pacient;
	Data dataConsultatie;
	List <String> simptome;
	String diagnostic; //xxx-denumire
	String BT; //ex: BTICJ 39976 otorinolaringologie
	String BI; //ex: BTIAS 72134
	String Rp; //ex: NTMLAI 15563
	
	private static int ct = 1000;
	
	/**
	 * Aceasta metoda reprezinta constructorul fara parametri al clasei
	 */
	public Consultatie()
	{
		this.IDconsultatie = ct++;
	}
	
	/**
	 * Aceasta metoda reprezinta constructorul cu parametri al clasei
  	 * @param p - pacientul
  	 * @param d - data
     * @param s - simptomele
	 * @param diag - diagnosticul
     * @param BT - biletul de trimitere
	 * @param BI - biletul de investigatii
	 * @param Rp - reteta
	 */
	public Consultatie (Pacient p, Data d, List <String> s, String diag, String BT, String BI, String Rp)
	{
		this.IDconsultatie = ct++;
		pacient = p;
		dataConsultatie = d;
		
		simptome = new ArrayList<String>();
		if (s.isEmpty())
			simptome.add("-");
		else
			simptome.addAll(s);
		
		diagnostic = diag;
		
		if (BT.length() < 3)
			this.BT = "-";
		else
			this.BT = BT;
		if (BI.length() < 3)
			this.BI = "-";
		else
			this.BI = BI;
		if (Rp.length() < 3)
			this.Rp = "-";
		else
			this.Rp = Rp;
	}
	
	/**
	 * Aceasta metoda reseteaza la valoarea initiala ID-ul consultatiilor,
	 * pentru a preveni incrementarea acestuia la rescriere si reafisare
	 */
	public void reset()
	{
		ct = 1000;
	}
	
	/**
	 * Aceasta metoda returneaza pacientul
   	 * @return pacientul
	 */
	public Pacient getPacient()
	{
		return pacient;
	}
	
	/**
	 * Aceasta metoda returneaza datele despre consultatie, pentru a fi stocate
	 * @return detaliile unei consultatii
	 */
	public String toText()
	{
		String rez = pacient.getNume().toUpperCase() + " " + pacient.getPrenume().toUpperCase() + " (" + pacient.getCNP() + ") " + dataConsultatie + " ";
		for (int i = 0; i < simptome.size() - 1; i++)
			rez += simptome.get(i) + ", ";
		rez += simptome.get(simptome.size() - 1) + " " + diagnostic + " BI " + BI + " BT " + BT + " R/p " + Rp;
		return rez;
	}
	
	/**
	 * Aceasta metoda returneaza informatiile detaliate despre o consultatie, pentru a fi afisate
	 * @return toate detaliile consultatiei
	 */
	public String toString()
	{
		String rez = IDconsultatie + " " + pacient.getNume().toUpperCase() + " " + pacient.getPrenume().toUpperCase() + " " + dataConsultatie + "\n         Simptome: ";
		rez = rez + simptome.get(0) + "\n";
		for (int i=1; i < simptome.size(); i++)
			rez = rez + "                            " + simptome.get(i) + "\n";
		rez = rez + "         Diagnostic: " + diagnostic + "\n";
		rez = rez + "         Reteta prescriptie: " + Rp + "\n";
		rez = rez + "         Bilet trimitere specialist: " + BT + "\n";
		rez = rez + "         Bilet investigatii: " + BI + "\n";
		return rez;
	}
}

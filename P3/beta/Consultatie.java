package EasyMed;

import java.util.*;

public class Consultatie
{
	final int IDconsultatie;
	Pacient pacient;
	Data dataConsultatie;
	List<String> simptome;
	String diagnostic; //cod-denumire
	String BT; //ex: BTICJ 39976 otorinolaringologie
	String BI; //ex: BTIAS 72134
	String Rp; //ex: NTMLAI 15563
	
	private static int ct=1000;
	
	public Consultatie()
	{
		this.IDconsultatie = ct++;
	}
	public Consultatie (Pacient p, Data d, List<String> s, String diag, String BT, String BI, String Rp)
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
	
	public String toText()
	{
		String rez = pacient.getNume().toUpperCase() + " " + pacient.getPrenume().toUpperCase() + " (" + pacient.getID() + ") " + dataConsultatie + " ";
		for (int i = 0; i < simptome.size() - 1; i++)
			rez += simptome.get(i) + ", ";
		rez += simptome.get(simptome.size() - 1) + " " + diagnostic + " BI " + BI + " BT " + BT + " R/p " + Rp;
		return rez;
	}
	public String toString()
	{
		String rez = IDconsultatie + " " + pacient.getNume().toUpperCase() + " " + pacient.getPrenume().toUpperCase() + " " + dataConsultatie + "\n     Simptome: ";
		rez = rez + simptome.get(0) + "\n";
		for (int i=1; i < simptome.size(); i++)
			rez = rez + "               " + simptome.get(i) + "\n";
		rez = rez + "     Diagnostic: " + diagnostic + "\n";
		rez = rez + "     Reteta prescriptie: " + Rp + "\n";
		rez = rez + "     Bilet trimitere specialist: " + BT + "\n";
		rez = rez + "     Bilet investigatii: " + BI + "\n";
		return rez;
	}
}

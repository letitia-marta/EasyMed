package EasyMed;

public class Pacient
{
	final int IDpacient;
	String nume;
	String prenume;
	String CNP;
	char sex; //F sau M
	Data dataNasterii;
	int varsta;
	Adresa adresa;
	
	private static int ct=100;
	
	public Pacient()
	{
		IDpacient = ct++;
		nume = "";
		prenume = "";
		CNP = "";
		sex = ' ';
	}
	
	public Pacient (String n, String p, String cnp, char s, Data d, Adresa a)
	{
		IDpacient = ct++;
		nume = n;
		prenume = p;
		CNP = cnp;
		sex = s;
		dataNasterii = new Data (d.zi, d.luna, d.an);
		adresa = new Adresa (a.strada, a.nr, a.oras, a.judet, a.tara);
	}
	
	public int getID()
	{
		return IDpacient;
	}
	public String getNume()
	{
		return nume;
	}
	public String getPrenume()
	{
		return prenume;
	}
	
	public void setNume (String n)
	{
		nume = n;
	}
	public void setPrenume (String p)
	{
		prenume = p;
	}
	public void setCNP (String cnp)
	{
		CNP = cnp;
	}
	public void setSex (char s)
	{
		sex = s;
	}
	public void setDataNasterii (Data d)
	{
		dataNasterii = d;
	}
	public void setAdresa (Adresa a)
	{
		adresa = a;
	}
	
	public void afisare()
	{
		System.out.println("NUME: " + nume + "\nPRENUME: " + prenume + "\nCNP: " + CNP + "\nSEX: " + sex + "\nDATA NASTERII: " + dataNasterii + "\nADRESA: " + adresa);
	}
	public void afisare (int indice)
	{
		String rez = "";
		if (indice < 10)
			rez += "00";
		else if (indice < 100)
			rez += "0";
		rez += indice + " " + nume.toUpperCase() + " " + prenume.toUpperCase();
		System.out.println(rez);
	}
	public String toText()
	{
		return nume + " " + prenume + " " + CNP + " " + sex + " " + dataNasterii + " " + adresa;
	}
	public String toString() //afisam doar preview
	{
		return nume.toUpperCase() + ' ' + prenume.toUpperCase();
	}
}

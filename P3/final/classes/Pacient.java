package classes;

/*
 * Aceasta clasa defineste un obiect de tipul pacient, cu caracteristicile sale:
 * nume si prenume, CNP, sex (M sau F), data nasterii si adresa domiciliului
 * 
 * Metode:
 * public Pacient() - constructor fara parametri
 * public Pacient (String, String, String, char, Data, Adresa) - constructor cu parametri
 * getteri si setteri pentru fiecare atribut al clasei
 * afisare() - metoda de afisare a datelor personale ale pacientului
 * afisare (int) - metoda de afisare a numelui complet al pacientului, precedat de un index
 * toText() - metoda de obtinere a datelor pacientului, pentru a fi stocate
 * toString() - metoda de obtinere a unui preview al pacientului, format din numele sau complet
 */
public class Pacient
{
	String nume;
	String prenume;
	String CNP;
	char sex; //F sau M
	Data dataNasterii;
	Adresa adresa;

 	/*
	 * Aceasta metoda reprezinta constructorul fara parametri al clasei
	 */
	public Pacient()
	{
		nume = "";
		prenume = "";
		CNP = "";
		sex = ' ';
	}

  	/*
	 * Aceasta metoda reprezinta constructorul cu parametri al clasei
  	 * @param n - nume
  	 * @param n - prenume
  	 * @param cnp - CNP
  	 * @param sex - sex
  	 * @param d - data nasterii
  	 * @param a - adresa
	 */
	public Pacient (String n, String p, String cnp, char s, Data d, Adresa a)
	{
		nume = n;
		prenume = p;
		CNP = cnp;
		sex = s;
		dataNasterii = new Data (d.zi, d.luna, d.an);
		adresa = new Adresa (a.strada, a.nr, a.oras, a.judet, a.tara);
	}

  	/*
	 * Aceasta metoda returneaza numele
   	 * @return numele
	 */
	public String getNume()
	{
		return nume;
	}

  	/*
	 * Aceasta metoda returneaza prenumele
   	 * @return prenumele
	 */
	public String getPrenume()
	{
		return prenume;
	}

  	/*
	 * Aceasta metoda returneaza CNP-ul
   	 * @return CNP-ul
	 */
	public String getCNP()
	{
		return CNP;
	}

  	/*
	 * Aceasta metoda returneaza sexul
   	 * @return sexul
	 */
	public char getSex()
	{
		return sex;
	}

  	/*
	 * Aceasta metoda returneaza data nasterii
   	 * @return data nasterii
	 */
	public Data getDataNasterii()
	{
		return dataNasterii;
	}

  	/*
	 * Aceasta metoda returneaza adresa
   	 * @return adresa
	 */
	public Adresa getAdresa()
	{
		return adresa;
	}

	/*
	 * Aceasta metoda atribuie numelui o noua valoare
   	 * @param n - noul nume
	 */
	public void setNume (String n)
	{
		nume = n;
	}

	/*
	 * Aceasta metoda atribuie prenumelui o noua valoare
   	 * @param p - noul prenume
	 */
	public void setPrenume (String p)
	{
		prenume = p;
	}

	/*
	 * Aceasta metoda atribuie CNP-ului o noua valoare
   	 * @param cnp - noul CNP
	 */
	public void setCNP (String cnp)
	{
		CNP = cnp;
	}

	/*
	 * Aceasta metoda atribuie sexului o noua valoare
   	 * @param s - noul sex
	 */
	public void setSex (char s)
	{
		sex = s;
	}
	
	/*
	 * Aceasta metoda atribuie datei nasterii o noua valoare
   	 * @param d - noua data
	 */
	public void setDataNasterii (Data d)
	{
		dataNasterii = d;
	}

	/*
	 * Aceasta metoda atribuie adresei o noua valoare
   	 * @param a - noua adresa
	 */
	public void setAdresa (Adresa a)
	{
		adresa = a;
	}
	
	/*
	 * Metoda de afisare a datelor personale ale pacientului
	 */
	public void afisare()
	{
		System.out.println("NUME: " + nume + "\nPRENUME: " + prenume + "\nCNP: " + CNP + "\nSEX: " + sex + "\nDATA NASTERII: " + dataNasterii + "\nADRESA: " + adresa);
	}
	
	/*
	 * Metoda de afisare a numelui complet al pacientului, precedat de un index
	 * @param indice indexul ce preceda numele pacientului, util in afisarea de multipli pacienti sub forma de lista 
	 */
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
	
	/*
	 * Aceasta metoda returneaza datele pacientului, pentru a fi stocate
	 * @return datele personale ale pacientului, insiruite
	 */
	public String toText()
	{
		return nume + " " + prenume + " " + CNP + " " + sex + " " + dataNasterii + " " + adresa + "\n";
	}
	
	/*
	 * Aceasta metoda returneaza un preview al pacientului, format din numele sau complet
	 * @return numele complet al pacientului
	 */
	public String toString()
	{
		return nume.toUpperCase() + ' ' + prenume.toUpperCase();
	}
}

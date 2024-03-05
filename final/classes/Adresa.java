package easymed;

/*
 * Aceasta clasa defineste un obiect de tipul adresa, cu caracteristicile sale:
 * strada, nr, oras, judet (in format cu 2 majuscule), tara
 * 
 * Metode:
 * public Adresa (String, String, String, String, String) - constructor cu parametri
 * getteri pentru fiecare atribut al clasei
 * toString() - metoda de obtinere detaliata a unei adrese
 */
public class Adresa
{
	String strada;
	String nr;
	String oras;
	String judet;
	String tara;

	/*
	 * Aceasta metoda reprezinta constructorul cu parametri al clasei
  	 * @param s - strada
  	 * @param n - numarul
  	 * @param o - orasul
  	 * @param j - judetul
  	 * @param t - tara
	 */
	public Adresa (String s, String n, String o, String j, String t)
	{
		strada = s;
		nr = n;
		oras = o;
		judet = j;
		tara = t;
	}

 	 /*
	 * Aceasta metoda returneaza strada
	 * @return strada
	 */
	public String getStrada()
	{
		return strada;
	}

  	/*
	 * Aceasta metoda returneaza numarul
	 * @return numarul
	 */
	public String getNr()
	{
		return nr;
	}

  	/*
	 * Aceasta metoda returneaza orasul
	 * @return orasul
	 */
	public String getOras()
	{
		return oras;
	}

  	/*
	 * Aceasta metoda returneaza judetul
	 * @return judetul
	 */
	public String getJudet()
	{
		return judet;
	}

  	/*
	 * Aceasta metoda returneaza tara
	 * @return tara
	 */
	public String getTara()
	{
		return tara;
	}
	
	/*
	 * Aceasta metoda returneaza informatiile componente ale unei adrese, pentru a fi afisate
	 * @return componentele adresei
	 */
	public String toString()
	{
		return "Str. " + strada + ", nr. " + nr + ", " + oras + ", " + judet + ", " + tara;
	}
}

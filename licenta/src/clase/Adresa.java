package clase;

/**
 * Aceasta clasa defineste un obiect de tipul adresa, cu caracteristicile sale:
 * strada, nr, oras, judet
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
	String bloc;
	String scara;
	String etaj;
	String apart;
	String oras;
	String judet;
	String tara;
	
	/**
	 * Aceasta metoda reprezinta constructorul cu parametri al clasei
  	 * @param s - strada
  	 * @param n - numarul
  	 * @param o - orasul
  	 * @param j - judetul
	 */
	public Adresa (String s, String n, String b, String sc, String e, String a, String o, String j)
	{
		strada = s;
		nr = n;
		bloc = b;
		scara = sc;
		etaj = e;
		apart = a;
		oras = o;
		judet = j;
	}
	
	/**
	 * Aceasta metoda returneaza strada
	 * @return strada
	 */
	public String getStrada()
	{
		return strada;
	}
	
	/**
	 * Aceasta metoda returneaza numarul
	 * @return numarul
	 */
	public String getNr()
	{
		return nr;
	}
	
	public String getBloc()
	{
		return bloc;
	}
	
	public String getScara()
	{
		return scara;
	}
	
	public String getEtaj()
	{
		return etaj;
	}
	
	public String getApart()
	{
		return apart;
	}
	
	/**
	 * Aceasta metoda returneaza orasul
	 * @return orasul
	 */
	public String getOras()
	{
		return oras;
	}
	
	/**
	 * Aceasta metoda returneaza judetul
	 * @return judetul
	 */
	public String getJudet()
	{
		return judet;
	}
	
	/**
	 * Aceasta metoda returneaza informatiile componente ale unei adrese, pentru a fi afisate
	 * @return componentele adresei
	 */
	public String toString()
	{
		return "Str. " + strada + ", nr. " + nr + ", " + oras + ", " + judet;
	}
}

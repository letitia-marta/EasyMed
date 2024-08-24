package clase;

/**
 * Aceasta clasa defineste un obiect de tipul data calendaristica, cu caracteristicile sale:
 * zi, luna si an
 * 
 * Metode:
 * public Data (int, int, int) - constructor cu parametri
 * getteri pentru fiecare atribut al clasei
 * toString() - metoda de obtinere a unei date calendaristice, in formatul zz.ll.aaaa
 */
public class Data
{
	int zi;
	int luna;
	int an;
	
	/**
	 * Aceasta metoda reprezinta constructorul cu parametri al clasei
  	 * @param z - ziua
	 * @param l - luna
	 * @param a - anul
	 */
	public Data (int z, int l, int a)
	{
		zi = z;
		luna = l;
		an = a;
	}
	
	/**
	 * Aceasta metoda returneaza ziua
	 * @return ziua
	 */
	public int getZi()
	{
		return zi;
	}
	
	/**
	 * Aceasta metoda returneaza luna
	 * @return luna
	 */
	public int getLuna()
	{
		return luna;
	}
	
	/**
	 * Aceasta metoda returneaza anul
	 * @return anul
	 */
	public int getAn()
	{
		return an;
	}
	
	/**
	 * Aceasta metoda returneaza data calendaristica in formatul zz.ll.aaaa
	 * @return data, in formatul zz.ll.aaaa
	 */
	public String toString()
	{
		String z = "" + zi;
		String l = "" + luna;
		if (zi <= 9)
			z = "0" + zi;
		if (luna <= 9)
			l = "0" + luna;
		return z + "." + l + "." + an;
	}
}

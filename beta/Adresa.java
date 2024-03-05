package EasyMed;

public class Adresa
{
	String strada;
	int nr;
	String oras;
	String judet;
	String tara;
	
	public Adresa (String s, int n, String o, String j, String t)
	{
		strada = s;
		nr = n;
		oras = o;
		judet = j;
		tara = t;
	}
	
	public String toString()
	{
		return "Str. " + strada + ", nr. " + nr + ", " + oras + ", " + judet + ", " + tara;
	}
}

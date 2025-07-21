package EasyMed;

public class Data
{
	int zi;
	int luna;
	int an;
	
	public Data (int z, int l, int a)
	{
		zi = z;
		luna = l;
		an = a;
	}
	
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

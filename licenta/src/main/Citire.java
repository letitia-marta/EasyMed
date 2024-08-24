package main;

import java.io.File;
import java.io.IOException;
import java.util.ArrayList;
import java.util.List;
import java.util.Scanner;

import clase.Adresa;
import clase.Consultatie;
import clase.Data;
import clase.Pacient;

public class Citire
{
	/**
	 * Aceasta metoda realizeaza citirea pacientilor din fisierul corespunzator,
	 * si incarcarea datelor obtinute intr-o lista
   	 * @param fisierPacienti - fisierul in care sunt stocate datele pacientilor
	 * @param pacienti - lista cu pacienti
	 */
	public static void citirePacienti (File fisierPacienti, List <Pacient> pacienti)
    {
    	try
		{
	    	Scanner scp = new Scanner(fisierPacienti);
	    	while (scp.hasNextLine())
			{
				String line = scp.nextLine();
				int i = 0;
				
				String nume = "";
				while (line.charAt(i) != ' ')
				{
					nume += line.charAt(i);
					i++;
				}
				
				String prenume = "";
				i++;
				while (line.charAt(i) != ' ')
				{
					prenume += line.charAt(i);
					i++;
				}
				
				String cnp = "";
				i++;
				while (line.charAt(i) != ' ')
				{
					cnp += line.charAt(i);
					i++;
				}
				
				i++;
				char sex = line.charAt(i);
				
				i += 2;
				int zi = ((int)line.charAt(i) - 48) * 10 + (int)line.charAt(i+1) - 48;
				i += 3;
				int luna = ((int)line.charAt(i) - 48) * 10 + (int)line.charAt(i+1) - 48;
				i += 3;
				int an = ((int)line.charAt(i) - 48) * 1000 + ((int)line.charAt(i+1) - 48) * 100 + ((int)line.charAt(i+2) - 48) * 10 + (int)line.charAt(i+3) - 48;;
				
				i += 5;
				while (line.charAt(i) != ' ')
					i++;
				i++;
				String strada = "";
				while (true)
				{
					if ((line.charAt(i) == 'n' && line.charAt(i+1) == 'r') || (line.charAt(i)) == ',')
						break;
					strada += line.charAt(i);
					i++;
				}
				
				while (line.charAt(i) < '0' || line.charAt(i) > '9')
					i++;
				String nr = "";
				while (line.charAt(i) != ' ' && line.charAt(i) != ',')
				{
					nr += line.charAt(i);
					i++;
				}
				
				String bloc = "";
				String scara = "";
				String etaj = "";
				String apart = "";
				
				while (line.charAt(i) != ' ')
					i++;
				i++;
				String oras = "";
				while (line.charAt(i) != ' ' && line.charAt(i) != ',')
				{
					oras += line.charAt(i);
					i++;
				}
				
				while (line.charAt(i) != ' ')
					i++;
				i++;
				String judet = "";
				judet += line.charAt(i);
				judet += line.charAt(i+1);
				
				String tara = "";
				if (line.endsWith(judet))
					tara = "Romania";
				else
				{
					i += 4;
					while (i < line.length())
					{
						tara += line.charAt(i);
						i++;
					}
				}
				
				Pacient pacient = new Pacient(nume, prenume, cnp, sex, new Data(zi, luna, an), new Adresa(strada, nr, bloc, scara, etaj, apart, oras,judet));
				pacienti.add(pacient);
			}
		}
    	catch (IOException e)
		{
			e.printStackTrace();
		}
    }
    
	/**
	 * Aceasta metoda realizeaza citirea consultatiilor din fisierul corespunzator,
	 * si incarcarea datelor obtinute intr-o lista
	 * @param fisierConsultatii - fisierul in care sunt stocate datele consultatiilor
	 * @param registru - lista cu consultatiile
	 * @param pacienti - lista cu pacientii
	 */
    public static void citireConsultatii (File fisierConsultatii, List <Consultatie> registru, List <Pacient> pacienti)
    {
    	try
		{
			Scanner scc = new Scanner(fisierConsultatii);
			List <String> simptome = new ArrayList <String> ();
			while (scc.hasNextLine())
			{
				String line = scc.nextLine();
				int i = 0;
				
				String nume = "";
				while (line.charAt(i) != ' ')
				{
					nume += line.charAt(i);
					i++;
				}
				
				String prenume = "";
				i++;
				while (line.charAt(i) != ' ')
				{
					prenume += line.charAt(i);
					i++;
				}
				
				String cnp = "";
				i += 2;
				while (line.charAt(i) != ')')
				{
					cnp += line.charAt(i);
					i++;
				}
				
				Pacient pacient = new Pacient();
				for (Pacient p: pacienti)
					if (p.getCNP().equals(cnp))
					{
						pacient = p;
						break;
					}
				
				i += 2;
				int zi = ((int)line.charAt(i) - 48) * 10 + (int)line.charAt(i+1) - 48;
				i += 3;
				int luna = ((int)line.charAt(i) - 48) * 10 + (int)line.charAt(i+1) - 48;
				i += 3;
				int an = ((int)line.charAt(i) - 48) * 1000 + ((int)line.charAt(i+1) - 48) * 100 + ((int)line.charAt(i+2) - 48) * 10 + (int)line.charAt(i+3) - 48;
				i += 5;
				
				simptome.clear();
				String diag = "";
				
				if (line.charAt(i) >= '0' && line.charAt(i) <= '9')
				{
					while (true)
					{
						if (line.charAt(i) == ' ' && line.charAt(i+1) == 'B' && line.charAt(i+2) == 'I')
							break;
						diag += line.charAt(i);
						i++;
					}
				}
				else
				{
					String simpt = "";
					while (true)
					{
						if (line.charAt(i) >= '0' && line.charAt(i) <= '9' && line.charAt(i+1) >= '0' && line.charAt(i+1) <= '9' && line.charAt(i+2) >= '0' && line.charAt(i+2) <= '9' && line.charAt(i+3) == '-')
							break;
						if (line.charAt(i) == ',' || (line.charAt(i) == ' ' && line.charAt(i+1) >= '0' && line.charAt(i+1) <= '9' && line.charAt(i+2) >= '0' && line.charAt(i+2) <= '9' && line.charAt(i+3) >= '0' && line.charAt(i+3) <= '9' && line.charAt(i+4) == '-'))
						{
							simptome.add(simpt);
							simpt = "";
							if (line.charAt(i) == ' ' && line.charAt(i+1) >= '0' && line.charAt(i+1) <= '9' && line.charAt(i+2) >= '0' && line.charAt(i+2) <= '9' && line.charAt(i+3) >= '0' && line.charAt(i+3) <= '9' && line.charAt(i+4) == '-')
								break;
							i += 2;
						}
						simpt += line.charAt(i);
						i++;
					}
					
					i++;
					while (true)
					{
						if (line.charAt(i) == ' ' && line.charAt(i+1) == 'B' && line.charAt(i+2) == 'I')
							break;
						diag += line.charAt(i);
						i++;
					}
				}
				
				i += 3;
				String bi = "";
				while (true)
				{
					if (line.charAt(i+1) == ' ' && line.charAt(i+2) == 'B' && line.charAt(i+3) == 'T' && line.charAt(i+4) == ' ')
						break;
					bi += line.charAt(i+1);
					i++;
				}
				
				i += 4;
				String bt = "";
				while (true)
				{
					if (line.charAt(i+1) == ' ' && line.charAt(i+2) == 'R' && line.charAt(i+3) == '/')
						break;
					bt += line.charAt(i+1);
					i++;
				}
				
				i += 6;
				String rp = "";
				if (line.endsWith(" 0") || line.endsWith(" -"))
					rp = "-";
				else
					while (i < line.length())
					{
						rp += line.charAt(i);
						i++;
					}
				
				Consultatie cons = new Consultatie(pacient, new Data(zi,luna,an), simptome, diag, bt, bi, rp);
				registru.add(cons);
			}
		}
    	catch (IOException e)
		{
			e.printStackTrace();
		}
    }
    
    /**
     * Aceasta metoda realizeaza citirea atat a pacientilor, cat si a consultatiilor,
     * prin apelarea consecutiva a celor doua metode definite mai sus
     * @param fisierPacienti - fisierul in care sunt stocate datele pacientilor
     * @param fisierConsultatii - fisierul in care sunt stocate datele consultatiilor
     * @param pacienti - lista cu pacientii
     * @param registru - lista cu consultatiile
     */
    public static void citire (File fisierPacienti, File fisierConsultatii, List <Pacient> pacienti, List <Consultatie> registru)
    {
    	try
		{
			Scanner scp = new Scanner(fisierPacienti);
			Scanner scc = new Scanner(fisierConsultatii);
		
			citirePacienti(fisierPacienti,pacienti);
			citireConsultatii(fisierConsultatii,registru,pacienti);
		}
		catch (IOException e)
		{
			e.printStackTrace();
		}
    }
    
    /**
     * Aceasta metoda returneaza detaliile unui anumit pacient din lista cu pacienti,
     * identificat prin numele sau complet
     * @param pacienti - lista cu pacientii
     * @param nume - numele complet al pacientului care se cauta
     * @return datele personale ale pacientului cautat
     */
    public static String detaliiPacient (List <Pacient> pacienti, String nume)
    {
    	String detalii = "";
    	for (Pacient p: pacienti)
    	{
    		String numeComplet = p.getNume().toUpperCase() + " " + p.getPrenume().toUpperCase();
    		if (numeComplet.equals(nume))
    			detalii += "NUME: " + p.getNume() + "\nPRENUME: " + p.getPrenume() + "\nCNP: " + p.getCNP() + "\nSEX: " + p.getSex() + "\nDATA NASTERII: " + p.getDataNasterii() + "\nADRESA: " + p.getAdresa();
    	}
    	return detalii;
    }
}

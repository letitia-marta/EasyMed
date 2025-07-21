package EasyMed;

import java.io.BufferedWriter;
import java.io.File;
import java.io.FileWriter;
import java.io.IOException;
import java.util.*;

public class Main
{	
	public static Pacient cautarePacient (List <Pacient> pacienti, List <Pacient> rezultate)
	{
		System.out.println("Cautare... ");
		Scanner nume = new Scanner(System.in);
		String n = nume.nextLine();
		
		int ct = 0;
		for (Pacient p: pacienti)
			if (p.getNume().toLowerCase().equals(n.toLowerCase()))
			{
				ct++;
				rezultate.add(p);
			}
		if (ct == 0)
		{
			System.out.println("Nu s-a gasit niciun pacient");
			return new Pacient();
		}
		else if (ct == 1)
		{
			System.out.println();
			rezultate.get(0).afisare();
			System.out.println();
			return rezultate.get(0);
		}
		else
		{
			System.out.println();
			for (int i = 0; i < rezultate.size(); i++)
				rezultate.get(i).afisare(rezultate.get(i).getID());
			
			System.out.println("Alegeti pacientul");
			int index;
			Scanner scIndex = new Scanner(System.in);
			index = scIndex.nextInt();
			
			Pacient pacient = new Pacient();
			for (Pacient p: rezultate)
				if (p.getID() == index)
				{
					pacient = p;
					break;
				}
			System.out.println();
			pacient.afisare();
			System.out.println();
			return pacient;
		}
	}
	
	public static void adaugarePacient (List<Pacient> pacienti)
	{
		System.out.println("Introduceti datele pacientului: ");
		
		System.out.println("Nume: ");
		Scanner nume = new Scanner(System.in);
		String num = nume.nextLine();
		//nume.close();
		
		System.out.println("Prenume: ");
		Scanner prenume = new Scanner(System.in);
		String pren = prenume.nextLine();
		//prenume.close();
		
		System.out.println("CNP: ");
		Scanner cnp = new Scanner(System.in);
		String c = cnp.nextLine();
		//cnp.close();
		
		System.out.println("Sex: ");
		Scanner sex = new Scanner(System.in);
		char s = sex.next().charAt(0);
		//sex.close();
		
		System.out.println("Data nasterii (in format ZZ/LL/AAAA): ");
		Scanner zi = new Scanner(System.in);
		int z = zi.nextInt();
		//zi.close();
		
		Scanner luna = new Scanner(System.in);
		int l = luna.nextInt();
		//luna.close();
		
		Scanner an = new Scanner(System.in);
		int a = an.nextInt();
		//an.close();
		
		System.out.println("Adresa (strada, nr, orasul, judetul, tara): ");
		Scanner strada = new Scanner(System.in);
		String str = strada.nextLine();
		//strada.close();
		
		Scanner nr = new Scanner(System.in);
		int n = nr.nextInt();
		//nr.close();
		
		Scanner oras = new Scanner(System.in);
		String o = oras.nextLine();
		//oras.close();
		
		Scanner judet = new Scanner(System.in);
		String j = judet.nextLine();
		//judet.close();
		
		Scanner tara = new Scanner(System.in);
		String t = tara.nextLine();
		//tara.close();
		
		Pacient p = new Pacient(num, pren, c, s, new Data(z,l,a), new Adresa(str,n,o,j,t));
		pacienti.add(p);
		
		String path = "pacienti.txt";
        try
        {
            FileWriter fileWriter = new FileWriter(path, true);
            BufferedWriter bufferedWriter = new BufferedWriter(fileWriter);
            bufferedWriter.write("\n" + p.toText());
            bufferedWriter.close();
            System.out.println("Pacientul a fost adaugat cu succes!");
        }
        catch (IOException e)
        {
            System.out.println("A intervenit o eroare!");
        }
	}
	
	public static void editarePacient (Pacient p, List <Pacient> pacienti)
	{
		int i = 0;
		for (Pacient pac: pacienti)
		{
			if (pacienti.get(i).equals(p))
			{
				System.out.println("Ce date doriti sa modificati?");
				System.out.println("(N)ume");
				System.out.println("(P)renume");
				System.out.println("(C)NP");
				System.out.println("(S)ex");
				System.out.println("(D)ata nasterii");
				System.out.println("(A)dresa");
				
				Scanner sc = new Scanner(System.in);
				char ch = sc.next().charAt(0);
				
				if (ch == 'N' || ch == 'n')
				{
					System.out.println("Introduceti numele: ");
					Scanner s = new Scanner(System.in);
					String nume = s.nextLine();
					p.setNume(nume);
					pacienti.set(i,p);
				}
				else if (ch == 'P' || ch == 'p')
				{
					System.out.println("Introduceti prenumele: ");
					Scanner s = new Scanner(System.in);
					String prenume = s.nextLine();
					p.setPrenume(prenume);
					pacienti.set(i,p);
				}
				else if (ch == 'C' || ch == 'c')
				{
					System.out.println("Introduceti CNP-ul: ");
					Scanner s = new Scanner(System.in);
					String cnp = s.nextLine();
					p.setCNP(cnp);
					pacienti.set(i,p);
				}
				else if (ch == 'S' || ch == 's')
				{
					System.out.println("Introduceti sexul: ");
					Scanner s = new Scanner(System.in);
					char sex = s.next().charAt(0);
					p.setSex(sex);
					pacienti.set(i,p);
				}
				else if (ch == 'D' || ch == 'd')
				{
					System.out.println("Introduceti data nasterii: ");
					Scanner s1 = new Scanner(System.in);
					Scanner s2 = new Scanner(System.in);
					Scanner s3 = new Scanner(System.in);
					int zi = s1.nextInt();
					int luna = s2.nextInt();
					int an = s3.nextInt();
					Data data = new Data(zi,luna,an);
					p.setDataNasterii(data);
					pacienti.set(i,p);
				}
				else if (ch == 'A' || ch == 'a')
				{
					System.out.println("Introduceti adresa: ");
					Scanner s1 = new Scanner(System.in);
					Scanner s2 = new Scanner(System.in);
					Scanner s3 = new Scanner(System.in);
					Scanner s4 = new Scanner(System.in);
					Scanner s5 = new Scanner(System.in);
					String strada = s1.nextLine();
					int nr = s2.nextInt();
					String oras = s3.nextLine();
					String judet = s4.nextLine();
					String tara = s5.nextLine();
					Adresa adresa = new Adresa(strada,nr,oras,judet,tara);
					p.setAdresa(adresa);
					pacienti.set(i,p);
				}
				else
					System.out.println("Optiune invalida. Incercati din nou.");
				
				String continut = "";
				for (int j = 0; j < pacienti.size() - 1; j++)
					continut += pacienti.get(j).toText() + "\n";
				continut += pacienti.get(pacienti.size() - 1).toText();
				
				String path = "pacienti.txt";
		        try
		        {
		            FileWriter fileWriter = new FileWriter(path);
		            BufferedWriter bufferedWriter = new BufferedWriter(fileWriter);
		            bufferedWriter.write(continut);
		            bufferedWriter.close();
		            System.out.println("Datele au fost actualizate!");
		        }
		        catch (IOException e)
		        {
		            e.printStackTrace();
		        }
		        
		        break;
			}
			i++;
		}
	}
	
	public static void stergerePacient (Pacient p, List <Pacient> pacienti)
	{
		for (int i = 0; i <= pacienti.size(); i++)
			if (pacienti.get(i) == p)
			{
				pacienti.remove(i);
				System.out.println("Pacientul a fost sters cu succes!");
				break;
			}
		
		String continut = "";
		for (int j = 0; j < pacienti.size() - 1; j++)
			continut += pacienti.get(j).toText() + "\n";
		continut += pacienti.get(pacienti.size() - 1).toText();
		
		String path = "pacienti.txt";
        try
        {
            FileWriter fileWriter = new FileWriter(path);
            BufferedWriter bufferedWriter = new BufferedWriter(fileWriter);
            bufferedWriter.write(continut);
            bufferedWriter.close();
        }
        catch (IOException e)
        {
            e.printStackTrace();
        }
	}
	
	public static void generareConsultatie (List <Consultatie> registru, List <Pacient> pacienti)
	{
		for (Pacient p: pacienti)
			System.out.println(p);
		System.out.println("Cautati un pacient: ");
		
		Scanner nume = new Scanner(System.in);
		String n = nume.nextLine();
		
		List <Pacient> rezultate = new ArrayList <Pacient> ();
		for (Pacient p: pacienti)
			if (p.getNume().toLowerCase().equals(n.toLowerCase()))
				rezultate.add(p);
		if (rezultate.isEmpty())
		{
			System.out.println("Pacient inexistent. Doriti sa adaugati un pacient nou?");
			adaugarePacient(pacienti);
		}
		else if (rezultate.size() == 1)
		{
			Pacient pacient = rezultate.get(0);
			System.out.println(pacient);
			
			String date = java.time.LocalDate.now().toString();
			int a1 = (int)date.charAt(0) - 48;
			int a2 = (int)date.charAt(1) - 48;
			int a3 = (int)date.charAt(2) - 48;
			int a4 = (int)date.charAt(3) - 48;
			int an = a1 * 1000 + a2 * 100 + a3 * 10 + a4;
			
			int l1 = (int)date.charAt(5) - 48;
			int l2 = (int)date.charAt(6) - 48;
			int luna = l1 * 10 + l2;
			
			int z1 = (int)date.charAt(8) - 48;
			int z2 = (int)date.charAt(9) - 48;
			int zi = z1 * 10 + z2;
			
			Data astazi = new Data(zi,luna,an);
			
			List <String> simptome = new ArrayList <String> ();
			System.out.println("Simptome: ");
			while (true)
			{
				Scanner sc = new Scanner(System.in);
				String simpt = sc.nextLine();
				if (simpt.equals("0"))
					break;
				simptome.add(simpt);
			}
			
			System.out.println("Diagnostic: ");
			Scanner sc = new Scanner(System.in);
			String diag = sc.nextLine();
			
			System.out.println("BT: ");
			Scanner sc1 = new Scanner(System.in);
			String bt = sc1.nextLine();
			if (bt == "0")
				bt = "0";
			
			System.out.println("BI: ");
			Scanner sc2 = new Scanner(System.in);
			String bi = sc2.nextLine();
			if (bi == "0")
				bi = "0";
			
			System.out.println("R/p: ");
			Scanner sc3 = new Scanner(System.in);
			String rp = sc3.nextLine();
			if (rp == "0")
				rp = "0";
			
			Consultatie cons = new Consultatie(pacient,astazi,simptome,diag,bt,bi,rp);
			registru.add(cons);
			
			String path = "consultatii.txt";
	        try
	        {
	            FileWriter fileWriter = new FileWriter(path, true);
	            BufferedWriter bufferedWriter = new BufferedWriter(fileWriter);
	            bufferedWriter.write("\n" + cons.toText());
	            bufferedWriter.close();
	            System.out.println("Pacientul a fost adaugat cu succes!");
	        }
	        catch (IOException e)
	        {
	            //e.printStackTrace();
	            System.out.println("A intervenit o eroare!");
	        }
	        
			System.out.println("Consultatia a fost generata!");
		}
		else if (rezultate.size() > 1)
		{
			for (Pacient p: rezultate)
				p.afisare(p.getID());
			System.out.println();
			System.out.println("Alegeti pacientul");
			int index;
			Scanner scIndex = new Scanner(System.in);
			index = scIndex.nextInt();
			
			Pacient pacient = new Pacient();
			for (Pacient p: rezultate)
				if (p.getID() == index)
				{
					pacient = p;
					break;
				}
			System.out.println(pacient);
			
			String date = java.time.LocalDate.now().toString();
			int a1 = (int)date.charAt(0) - 48;
			int a2 = (int)date.charAt(1) - 48;
			int a3 = (int)date.charAt(2) - 48;
			int a4 = (int)date.charAt(3) - 48;
			int an = a1 * 1000 + a2 * 100 + a3 * 10 + a4;
			
			int l1 = (int)date.charAt(5) - 48;
			int l2 = (int)date.charAt(6) - 48;
			int luna = l1 * 10 + l2;
			
			int z1 = (int)date.charAt(8) - 48;
			int z2 = (int)date.charAt(9) - 48;
			int zi = z1 * 10 + z2;
			
			Data astazi = new Data(zi,luna,an);
			
			List <String> simptome = new ArrayList <String> ();
			System.out.println("Simptome: ");
			while (true)
			{
				Scanner sc = new Scanner(System.in);
				String simpt = sc.nextLine();
				if (simpt.equals("0"))
					break;
				simptome.add(simpt);
			}
			
			System.out.println("Diagnostic: ");
			Scanner sc = new Scanner(System.in);
			String diag = sc.nextLine();
			
			System.out.println("BT: ");
			Scanner sc1 = new Scanner(System.in);
			String bt = sc1.nextLine();
			if (bt.equals("0"))
				bt = "0";
			
			System.out.println("BI: ");
			Scanner sc2 = new Scanner(System.in);
			String bi = sc2.nextLine();
			if (bi.equals("0"))
				bi = "0";
			
			System.out.println("R/p: ");
			Scanner sc3 = new Scanner(System.in);
			String rp = sc3.nextLine();
			if (rp.equals("0"))
				rp = "0";
			
			Consultatie cons = new Consultatie(pacient,astazi,simptome,diag,bt,bi,rp);
			registru.add(cons);
			System.out.println(cons);
			String path = "consultatii.txt";
	        try
	        {
	            FileWriter fileWriter = new FileWriter(path, true);
	            BufferedWriter bufferedWriter = new BufferedWriter(fileWriter);
	            bufferedWriter.write("\n" + cons.toText());
	            bufferedWriter.close();
	            System.out.println("Pacientul a fost adaugat cu succes!");
	        }
	        catch (IOException e)
	        {
	            //e.printStackTrace();
	            System.out.println("A intervenit o eroare!");
	        }
	        
			System.out.println("Consultatia a fost generata!");
		}
	}
	
	public static void meniu (List <Pacient> pacienti, List <Consultatie> registru)
	{
		System.out.println("-BINE ATI VENIT-");
		System.out.println("(P)acienti");
		System.out.println("(R)egistru consultatii");
		
		char optiune;
		Scanner sc1 = new Scanner(System.in);
		optiune = sc1.next().charAt(0);
		
		if (optiune == 'P' || optiune == 'p')
		{
			System.out.println();
			System.out.println("-Pacienti-");
			for (Pacient p: pacienti)
				System.out.println(p);
			
			System.out.println();
			System.out.println("(C)autare pacienti");
			System.out.println("(A)daugare pacient nou");
			
			char op;
			Scanner sc2 = new Scanner(System.in);
			op = sc2.next().charAt(0);
			
			if (op == 'C' || op == 'c')
			{
				List <Pacient> rezultate = new ArrayList <Pacient> ();
				Pacient pacient = cautarePacient(pacienti,rezultate);
				
				System.out.println("(E)ditare date pacient");
				System.out.println("(S)tergere pacient");
				
				char o;
				Scanner sc3 = new Scanner(System.in);
				o = sc3.next().charAt(0);
				
				if (o == 'E' || o == 'e')
					editarePacient(pacient,pacienti);
				else if (o == 'S' || o == 's')
					stergerePacient(pacient,pacienti);
				else
					System.out.println("Optiune invalida. Incercati din nou.");
			}
			else if (op == 'A' || op == 'a')
				adaugarePacient(pacienti);
			else
				System.out.println("Optiune invalida. Incercati din nou.");
		}
		else if (optiune == 'R' || optiune == 'r')
		{
			System.out.println();
			//Runtime.getRuntime().exec("cls");
			System.out.println("-Registru consultatii-\n");
			for (Consultatie c: registru)
				System.out.println(c);
			
			System.out.println();
			System.out.println("(G)enerare consultatie");
			
			char op;
			Scanner sc2 = new Scanner(System.in);
			op = sc2.next().charAt(0);
			
			if (op == 'G' || op == 'g')
			{
				generareConsultatie(registru,pacienti);
				for (Consultatie c: registru)
					System.out.println(c);
			}
		}
		else
		{
			System.out.println("Optiune invalida. Incercati din nou.");
			meniu(pacienti,registru);
		}
	}
	
	public static void main (String[] args)
	{
		File fisierPacienti = new File("pacienti.txt");
		File fisierConsultatii = new File("consultatii.txt");
		
		try
		{
			Scanner scp = new Scanner(fisierPacienti);
			Scanner scc = new Scanner(fisierConsultatii);
			
			List <Pacient> pacienti = new ArrayList <Pacient> ();
			List <Consultatie> registru = new ArrayList <Consultatie> ();
			
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
				String nrstr = "";
				while (line.charAt(i) != ' ' && line.charAt(i) != ',')
				{
					nrstr += line.charAt(i);
					i++;
				}
				int ordin = nrstr.length() - 1;
				int zece = 1;
				for (int p = 1; p <= ordin; p++)
					zece *= 10;
				int nr = 0;
				for (int j = 0; j <= ordin; j++)
				{
					nr += ((int)nrstr.charAt(j) - 48) * zece;
					zece /= 10;
				}
				
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
				
				Pacient pacient = new Pacient(nume,prenume,cnp,sex,new Data(zi,luna,an),new Adresa(strada,nr,oras,judet,tara));
				pacienti.add(pacient);
			}
			
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
				
				String idstr = "";
				i += 2;
				while (line.charAt(i) != ')')
				{
					idstr += line.charAt(i);
					i++;
				}
				int ordin = idstr.length() - 1;
				int zece = 1;
				for (int p = 1; p <= ordin; p++)
					zece *= 10;
				int id = 0;
				for (int j = 0; j <= ordin; j++)
				{
					id += ((int)idstr.charAt(j) - 48) * zece;
					zece /= 10;
				}
				
				Pacient pacient = new Pacient();
				for (Pacient p: pacienti)
					if (p.getID() == id)
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
			
			meniu(pacienti,registru);
		}
		catch (IOException e)
		{
			e.printStackTrace();
		}
	}
}

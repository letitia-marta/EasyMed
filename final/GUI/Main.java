package GUI;

import java.util.Collections;
import java.util.Comparator;
import javax.swing.*;
import javax.swing.event.ListSelectionEvent;
import javax.swing.event.ListSelectionListener;

import easymed.Adresa;
import easymed.Consultatie;
import easymed.Data;
import easymed.Pacient;

import java.awt.*;
import java.awt.event.ActionEvent;
import java.awt.event.ActionListener;
import java.awt.event.MouseAdapter;
import java.awt.event.MouseEvent;
import java.io.BufferedWriter;
import java.io.File;
import java.io.FileWriter;
import java.io.IOException;
import java.time.LocalDate;
import java.time.Year;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.List;
import java.util.Properties;
import java.util.Scanner;

public class Main
{
	/*
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
		}
    	catch (IOException e)
		{
			e.printStackTrace();
		}
    }
    
  /*
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
    
    /*
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
    
    /*
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
    
    /*
     * Aceasta metoda umple cu informatii campurile pentru pacienti, respectiv consultatii
     * @param campPacienti - campul unde se afiseaza, sub forma de lista, pacientii
     * @param campConsultatii - campul unde se afiseaza datele despre consultatii
     * @param afisare - lista de pacienti care va fi afisata
     * @param registru - lista cu consultatiile
     */
    public static void afisare (JTextArea campPacienti, JTextArea campConsultatii, List <Pacient> afisare, List <Consultatie> registru)
    {
    	String text = "";
        int i = 1;
        for (Pacient p : afisare)
        {
        	text += i + ". " + p + "\n";
        	i++;
        }
        campPacienti.setText(text);

        text = "";
        for (Consultatie c : registru)
            text += c + "\n";
        campConsultatii.setText(text);
    }

    /*
     * Aceasta metoda defineste actiunea indeplinita de butonul "Acasa", si anume
     * recitirea si reincarcarea datelor in campurile corespuzatoare
     * @param home - butonul din meniu caruia i se adauga actiunea
     * @param pacienti - lista cu pacienti
     * @param afisare - lista cu pacienti care va fi afisata
     * @param registru - lista cu consultatii
     * @param fisierPacienti - fisierul in care sunt stocati pacientii
     * @param fisierConsultatii - fisierul in care sunt stocate consultatiile
     */
    public static void homeAct (JMenuItem home, List <Pacient> pacienti, List <Pacient> afisare, List <Consultatie> registru, File fisierPacienti, File fisierConsultatii)
    {
        home.addActionListener (new ActionListener()
        {
            @Override
            public void actionPerformed (ActionEvent e)
            {
                JFrame newFrame = new JFrame();
                newFrame.setTitle("EasyMed");
                newFrame.setSize(800, 600);
                newFrame.setExtendedState(JFrame.MAXIMIZED_BOTH);
                newFrame.setLayout(new GridLayout(0,2));
            	
            	JTextField search;
            	JButton searchBtn;
                JLabel etichetaPacienti;
                JLabel etichetaConsultatii;
                JTextArea campPacienti;
                JTextArea campConsultatii;
                JScrollPane scrollPacienti;
                JScrollPane scrollConsultatii;
                
                campPacienti = new JTextArea(100,200);
                campConsultatii = new JTextArea(100,200);
                scrollPacienti = new JScrollPane();
                scrollConsultatii = new JScrollPane();
                
                try
                {
                    UIManager.setLookAndFeel("javax.swing.plaf.nimbus.NimbusLookAndFeel");
                }
                catch (Exception ex)
                {
                    ex.printStackTrace();
                }
                
                //CITIRE
                pacienti.clear();
        		registru.get(0).reset();
        		registru.clear();
        		citire(fisierPacienti,fisierConsultatii,pacienti,registru);
        		
        		afisare.clear();
                afisare.addAll(pacienti);
                Collections.sort (afisare, new Comparator <Pacient> ()
                {
                    @Override
                    public int compare (Pacient p1, Pacient p2)
                    {
                        int rez = p1.getNume().compareToIgnoreCase(p2.getNume());
                        if (rez == 0)
                            rez = p1.getPrenume().compareToIgnoreCase(p2.getPrenume());
                        return rez;
                    }
                });

                //MENIU
                meniu(newFrame,pacienti,afisare,registru,campPacienti,campConsultatii,scrollPacienti,scrollConsultatii,fisierPacienti,fisierConsultatii);
                
                //AFISARE
                afisare(campPacienti,campConsultatii,afisare,registru);
                scrollPacienti.setViewportView(campPacienti);
                scrollConsultatii.setViewportView(campConsultatii);
                
                //FORMAT
                campPacienti.setBackground(Color.DARK_GRAY);
                campPacienti.setForeground(Color.WHITE);
                campConsultatii.setBackground(Color.DARK_GRAY);
                campConsultatii.setForeground(Color.WHITE);

                GroupLayout layout = new GroupLayout(newFrame.getContentPane());
                newFrame.getContentPane().setLayout(layout);

                layout.setAutoCreateGaps(true);
                layout.setAutoCreateContainerGaps(true);

                GroupLayout.SequentialGroup hGroup = layout.createSequentialGroup();
                hGroup.addGroup(layout.createParallelGroup().addComponent(scrollPacienti));
                hGroup.addGroup(layout.createParallelGroup().addComponent(scrollConsultatii));
                layout.setHorizontalGroup(hGroup);

                GroupLayout.SequentialGroup vGroup = layout.createSequentialGroup();
                vGroup.addGroup(layout.createParallelGroup(GroupLayout.Alignment.BASELINE).addComponent(scrollPacienti).addComponent(scrollConsultatii));
                layout.setVerticalGroup(vGroup);

                newFrame.getContentPane().setBackground(Color.BLACK);
                newFrame.setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
                newFrame.setVisible(true);
            }
        });
    }
    
    /*
     * Aceasta metoda defineste actiunea indeplinita de butonul "Generare consultatie", si anume
     * posibilitatea introducerii de date pentru a fi creeata o noua consultatie, implicit pe data curenta
     * @param generareMenu - butonul din meniu caruia i se adauga actiunea
     * @param mainFrame - fereastra principala a aplicatiei
     * @param pacienti - lista cu pacienti
     * @param afisare - lista cu pacienti care va fi afisata
     * @param registru - lista cu consultatii
     * @param campPacienti - campul in care sunt afisati pacientii
     * @param campConsultatii - campul in care sunt afisate consultatiile
     * @param scrollPacienti - scroll-ul ce cuprinde campul cu pacienti
     * @param scrollConsultatii - scroll-ul ce cuprinde campul cu consultatii
     * @param fisierPacienti - fisierul in care sunt stocati pacientii
     * @param fisierConsultatii - fisierul in care sunt stocate consultatiile
     */
    public static void generareAct (JMenuItem generareMenu, JFrame mainFrame, List <Pacient> pacienti, List <Pacient> afisare, List <Consultatie> registru, JTextArea campPacienti, JTextArea campConsultatii, JScrollPane scrollPacienti, JScrollPane scrollConsultatii, File fisierPacienti, File fisierConsultatii)
    {
    	generareMenu.addActionListener (new ActionListener()
        {
        	@Override
            public void actionPerformed (ActionEvent e)
            {
        		JFrame ecranGenerare = new JFrame("Generare consultatie");
        		ecranGenerare.setSize(800, 600);
        		ecranGenerare.setExtendedState(JFrame.MAXIMIZED_BOTH);
                
        		GridBagConstraints gbc = new GridBagConstraints();
        		gbc.insets = new Insets(5, 5, 5, 5);
        		JPanel panel = new JPanel(new GridBagLayout());
        		
        		ecranGenerare.setJMenuBar(meniu(mainFrame,pacienti,afisare,registru,campPacienti,campConsultatii,scrollPacienti,scrollConsultatii,fisierPacienti,fisierConsultatii));
                
                JLabel labelNume = new JLabel("Pacient: ");
                JLabel labelZi = new JLabel("Zi: ");
                JLabel labelLuna = new JLabel("Luna: ");
                JLabel labelAn = new JLabel("An: ");
                JLabel labelSimptome = new JLabel("Simptome: ");
                JLabel labelDiagostic = new JLabel("Diagnostic: ");
                JLabel labelBT = new JLabel("BT: ");
                JLabel labelBI = new JLabel("BI: ");
                JLabel labelRp = new JLabel("R/p: ");
                
                JComboBox <String> pacientiOptiuni = new JComboBox <String> ();
	            for (Pacient p: afisare)
	            {
	            	String numeComplet = p.getNume().toUpperCase() + " " + p.getPrenume().toUpperCase();
	                pacientiOptiuni.addItem(numeComplet);
	            }
	            pacientiOptiuni.setEditable(false);
	            
	            SpinnerModel ziSpinnerModel = new SpinnerNumberModel(LocalDate.now().getDayOfMonth(), 1, 31, 1);
	            JSpinner ziSpinner = new JSpinner(ziSpinnerModel);

	            SpinnerModel lunaSpinnerModel = new SpinnerNumberModel(LocalDate.now().getMonthValue(), 1, 12, 1);
	            JSpinner lunaSpinner = new JSpinner(lunaSpinnerModel);

	            int anCurent = Year.now().getValue();
	            SpinnerModel anSpinnerModel = new SpinnerNumberModel(anCurent, 1, anCurent, 1);
	            JSpinner anSpinner = new JSpinner(anSpinnerModel);
                
	            JTextArea campSimptome = new JTextArea();
	            campSimptome.setRows(8);
	            campSimptome.setColumns(30);
	            campSimptome.setPreferredSize(new Dimension(300, 200));
	            campSimptome.setLineWrap(true);
	            
                JTextField campDiagnostic = new JTextField();
                JTextField campBT = new JTextField();
                JTextField campBI = new JTextField();
                JTextField campRp = new JTextField();
                
                JButton butonSalvare = new JButton("Adauga");
                butonSalvare.addActionListener (new ActionListener()
                {
                    @Override
                    public void actionPerformed (ActionEvent e)
                    {
                    	String nume = (String) pacientiOptiuni.getSelectedItem();
                        int zi = (int)ziSpinner.getValue();
                        int luna = (int)lunaSpinner.getValue();
                        int an = (int)anSpinner.getValue();
                        
                        List <String> simptome = new ArrayList <String> ();
                        String[] randuri = campSimptome.getText().split("\n");
                        for (String r: randuri)
                        	simptome.add(r.trim());
                        
                        String diag = campDiagnostic.getText();
                        String bt = campBT.getText();
                        String bi = campBI.getText();
                        String rp = campRp.getText();
                        
                        Pacient pacient = new Pacient();
                        for (Pacient p: pacienti)
                        	if (p.toString().equals(nume))
                        	{
                        		pacient = p;
                        		break;
                        	}
                        
                    	Consultatie cons = new Consultatie(pacient,new Data(zi,luna,an),simptome,diag,bt,bi,rp);
	                    registru.add(cons);
	                    String path = "consultatii.txt";
	        	        try
	        	        {
	        	            FileWriter fileWriter = new FileWriter(path, true);
	        	            BufferedWriter bufferedWriter = new BufferedWriter(fileWriter);
	        	            bufferedWriter.write("\n" + cons.toText());
	        	            bufferedWriter.close();
	        	        }
	        	        catch (IOException ex)
	        	        {
	        	            System.out.println("A intervenit o eroare!");
	        	        }
	                    ecranGenerare.dispose();
                    }
                });
                
                JPanel utilitar = new JPanel();
                
                gbc.gridx = 0;
                gbc.gridy = 0;
                panel.add(labelNume, gbc);

                gbc.gridx = 1;
                gbc.gridy = 0;
                panel.add(pacientiOptiuni, gbc);

                gbc.gridx = 0;
                gbc.gridy = 1;
                panel.add(labelZi, gbc);

                gbc.gridx = 1;
                panel.add(ziSpinner, gbc);

                gbc.gridx = 2;
                gbc.gridy = 1;
                panel.add(labelLuna, gbc);

                gbc.gridx = 3;
                panel.add(lunaSpinner, gbc);

                gbc.gridx = 4;
                gbc.gridy = 1;
                panel.add(labelAn, gbc);

                gbc.gridx = 5;
                panel.add(anSpinner, gbc);

                gbc.gridx = 0;
                gbc.gridy = 2;
                panel.add(utilitar, gbc);

                gbc.gridx = 0;
                gbc.gridy = 3;
                panel.add(labelSimptome, gbc);

                gbc.gridx = 1;
                gbc.gridy = 4;
                gbc.gridwidth = 5;
                gbc.gridheight = 4;
                gbc.fill = GridBagConstraints.BOTH;
                panel.add(new JScrollPane(campSimptome), gbc);

                gbc.gridx = 0;
                gbc.gridy = 8;
                panel.add(labelDiagostic, gbc);

                gbc.gridx = 1;
                panel.add(campDiagnostic, gbc);

                gbc.gridx = 0;
                gbc.gridy = 9;
                gbc.gridheight = 4;
                panel.add(utilitar, gbc);

                gbc.gridx = 0;
                gbc.gridy = 13;
                panel.add(labelBT, gbc);

                gbc.gridx = 1;
                panel.add(campBT, gbc);

                gbc.gridx = 0;
                gbc.gridy = 15;
                gbc.gridheight = 2;
                panel.add(utilitar, gbc);

                gbc.gridx = 0;
                gbc.gridy = 17;
                panel.add(labelBI, gbc);

                gbc.gridx = 1;
                panel.add(campBI, gbc);

                gbc.gridx = 0;
                gbc.gridy = 19;
                panel.add(labelRp, gbc);

                gbc.gridx = 1;
                panel.add(campRp, gbc);

                gbc.gridx = 0;
                gbc.gridy = 21;
                gbc.gridheight = 2;
                panel.add(utilitar, gbc);

                gbc.gridx = 0;
                gbc.gridy = 23;
                panel.add(butonSalvare, gbc);
                
                ecranGenerare.add(panel);
                
                ecranGenerare.getContentPane().setBackground(Color.BLACK);
                panel.setBackground(Color.DARK_GRAY);
                panel.setForeground(Color.WHITE);
                utilitar.setBackground(Color.DARK_GRAY);
                utilitar.setForeground(Color.WHITE);
                labelNume.setForeground(Color.WHITE);
                labelZi.setForeground(Color.WHITE);
                labelLuna.setForeground(Color.WHITE);
                labelAn.setForeground(Color.WHITE);
                labelSimptome.setForeground(Color.WHITE);
                labelDiagostic.setForeground(Color.WHITE);
                labelBT.setForeground(Color.WHITE);
                labelBI.setForeground(Color.WHITE);
                labelRp.setForeground(Color.WHITE);
                butonSalvare.setBackground(Color.CYAN);
                butonSalvare.setForeground(Color.BLACK);
                
                ecranGenerare.setDefaultCloseOperation(JFrame.DISPOSE_ON_CLOSE);
                ecranGenerare.setVisible(true);
            }
        });
    }
    
    /*
     * Aceasta metoda defineste actiunea indeplinita de butonul "Cauta un pacient", si anume
     * cautarea in lista de pacienti si afisarea tuturor pacientilor care corespund numelui cautat,
     * cu afisarea datelor personale si posibilitatea editarii acestora, a stergerii pacientului si
     * a generarii unei consultatii pentru pacientul respectiv
     * @param cautareMenu - butonul din meniu caruia i se adauga actiunea
     * @param mainFrame - fereastra principala a aplicatiei
     * @param generareMenu - butonul pentru generarea unei consultatii
     * @param pacienti - lista cu pacienti
     * @param afisare - lista cu pacienti care va fi afisata
     * @param registru - lista cu consultatii
     * @param campPacienti - campul in care sunt afisati pacientii
     * @param campConsultatii - campul in care sunt afisate consultatiile
     * @param scrollPacienti - scroll-ul ce cuprinde campul cu pacienti
     * @param scrollConsultatii - scroll-ul ce cuprinde campul cu consultatii
     * @param fisierPacienti - fisierul in care sunt stocati pacientii
     * @param fisierConsultatii - fisierul in care sunt stocate consultatiile
     */
    public static void cautareAct (JMenuItem cautareMenu, JFrame mainFrame, JMenuItem generareMenu, List <Pacient> pacienti, List <Pacient> afisare, List <Consultatie> registru, JTextArea campPacienti, JTextArea campConsultatii, JScrollPane scrollPacienti, JScrollPane scrollConsultatii, File fisierPacienti, File fisierConsultatii)
    {
    	cautareMenu.addActionListener (new ActionListener()
        {
            @Override
            public void actionPerformed (ActionEvent e)
            {
            	JFrame ecranCautare = new JFrame("Cautare...");
                ecranCautare.setSize(800, 600);
                ecranCautare.setExtendedState(JFrame.MAXIMIZED_BOTH);
                
                ecranCautare.setJMenuBar(meniu(mainFrame,pacienti,afisare,registru,campPacienti,campConsultatii,scrollPacienti,scrollConsultatii,fisierPacienti,fisierConsultatii));

                JTextField search = new JTextField(20);
                JButton searchBtn = new JButton("Cauta");
                JLabel label = new JLabel("Optiuni");
                JLabel cons = new JLabel("Istoric consultatii");

                DefaultListModel <String> lista = new DefaultListModel <String> ();
                JList <String> rezultate = new JList <String> (lista);
                rezultate.setMinimumSize(new Dimension(200,1000));

                JTextArea campDetalii = new JTextArea(100,200);
                JTextArea campCons = new JTextArea(100,200);

                JPanel optiuni = new JPanel();
                optiuni.setLayout(new BoxLayout(optiuni, BoxLayout.Y_AXIS));
                JButton editBtn = new JButton("Editare date pacient");
                JButton deleteBtn = new JButton("Eliminare pacient");
                JButton consultBtn = new JButton("Genereaza consultatie");
       
                ecranCautare.setLayout(new BoxLayout(ecranCautare.getContentPane(), BoxLayout.Y_AXIS));
                ecranCautare.add(search);
                ecranCautare.add(searchBtn);
                ecranCautare.add(cons);
                ecranCautare.add(label);
                ecranCautare.add(rezultate);
                ecranCautare.add(campDetalii);
                ecranCautare.add(campCons);
                ecranCautare.add(optiuni);
                
                //CAUTARE
                searchBtn.addActionListener (new ActionListener()
                {
                    @Override
                    public void actionPerformed (ActionEvent e)
                    {
                        String nume = search.getText();
                        DefaultListModel <String> newListModel = new DefaultListModel <String> ();
                        for (Pacient p : pacienti)
                            if (p.getNume().toLowerCase().equals(nume.toLowerCase()))
                                newListModel.addElement(p.toString());
                        rezultate.setModel(newListModel);
                    }
                });

                //REZULTATE
                rezultate.addListSelectionListener (new ListSelectionListener()
                {
                    @Override
                    public void valueChanged (ListSelectionEvent e)
                    {
                        if (!e.getValueIsAdjusting())
                        {
                            String numePacient = rezultate.getSelectedValue();
                            if (numePacient != null)
                            {
                            	String consultatii = "";
                            	Pacient pacient = new Pacient();
                            	for (Pacient p: pacienti)
                            	{
                            		String numeComplet = p.getNume().toUpperCase() + " " + p.getPrenume().toUpperCase();
                            		if (numeComplet.equals(numePacient))
                            		{
                            			pacient = p;
                            			break;
                            		}
                            	}
                            	for (Consultatie c: registru)
                            		if (c.getPacient().equals(pacient))
                            			consultatii += c + "\n";
                            	
                                campDetalii.setText(detaliiPacient(pacienti,numePacient));
                                campCons.setText(consultatii);
                                label.setForeground(Color.WHITE);
                                cons.setForeground(Color.WHITE);
                                optiuni.add(editBtn);
                                optiuni.add(consultBtn);
                                optiuni.add(deleteBtn);
                                
                                //EDITARE
                                editBtn.addActionListener (new ActionListener()
                                {
                                    @Override
                                    public void actionPerformed (ActionEvent e)
                                    {
                                    	Pacient pacient = new Pacient();
                                    	final int[] i = {0};
                                    	for (Pacient p: pacienti)
                                    	{
                                    		String numeComplet = p.getNume().toUpperCase() + " " + p.getPrenume().toUpperCase();
                                    		if (numeComplet.equals(numePacient))
                                    		{
                                    			pacient = p;
                                    			break;
                                    		}
                                    		i[0] ++;
                                    	}
                                    	
                                        JFrame ecranDatePersonale = new JFrame("Editare date pacient");
                                        ecranDatePersonale.setSize(800, 600);
                                        ecranDatePersonale.setExtendedState(JFrame.MAXIMIZED_BOTH);
                                        
                                        ecranDatePersonale.setJMenuBar(meniu(mainFrame,pacienti,afisare,registru,campPacienti,campConsultatii,scrollPacienti,scrollConsultatii,fisierPacienti,fisierConsultatii));
                                        
                                        JPanel panel = new JPanel();
                                        panel.setLayout(new GridLayout(14, 2, 10, 10));
                                        
                                        JLabel labelNume = new JLabel("Nume: ");
                                        JLabel labelPrenume = new JLabel("Prenume: ");
                                        JLabel labelCNP = new JLabel("CNP: ");
                                        JLabel labelSex = new JLabel("Sex: ");
                                        JLabel labelZi = new JLabel("Zi: ");
                                        JLabel labelLuna = new JLabel("Luna: ");
                                        JLabel labelAn = new JLabel("An: ");
                                        JLabel labelStrada = new JLabel("Strada: ");
                                        JLabel labelNr = new JLabel("Nr.: ");
                                        JLabel labelOras = new JLabel("Oras: ");
                                        JLabel labelJudet = new JLabel("Judet: ");
                                        JLabel labelTara = new JLabel("Tara: ");
                                        
                                        JTextField campNume = new JTextField();
                                        campNume.setText(pacient.getNume());
                                        JTextField campPrenume = new JTextField();
                                        campPrenume.setText(pacient.getPrenume());
                                        JTextField campCNP = new JTextField();
                                        campCNP.setText(pacient.getCNP());
                                        char[] sexOptions = {'M', 'F'};
                                        JComboBox <Character> sex = new JComboBox<>();
                                        for (char option: sexOptions)
                                            sex.addItem(option);
                                        sex.setSelectedItem(pacient.getSex());
                                        sex.setEditable(false);
                                        
                                        int ziValue = pacient.getDataNasterii().getZi();
                                        SpinnerModel ziSpinnerModel = new SpinnerNumberModel(ziValue, 1, 31, 1);
                                        JSpinner ziSpinner = new JSpinner(ziSpinnerModel);
                                        
                                        int lunaValue = pacient.getDataNasterii().getLuna();
                                        SpinnerModel lunaSpinnerModel = new SpinnerNumberModel(lunaValue, 1, 12, 1);
                                        JSpinner lunaSpinner = new JSpinner(lunaSpinnerModel);
                                        
                                        int anValue = pacient.getDataNasterii().getAn();
                                        int anCurent = Year.now().getValue();
                                        SpinnerModel anSpinnerModel = new SpinnerNumberModel(anValue, 1, anCurent, 1);
                                        JSpinner anSpinner = new JSpinner(anSpinnerModel);
                                        
                                        JTextField campStrada = new JTextField();
                                        campStrada.setText(pacient.getAdresa().getStrada());
                                        JTextField campNr = new JTextField();
                                        campNr.setText(pacient.getAdresa().getNr() + "");
                                        JTextField campOras = new JTextField();
                                        campOras.setText(pacient.getAdresa().getOras());
                                        JTextField campJudet = new JTextField();
                                        campJudet.setText(pacient.getAdresa().getJudet());
                                        JTextField campTara = new JTextField();
                                        campTara.setText(pacient.getAdresa().getTara());
                                        
                                        JButton butonSalvare = new JButton("Salveaza");
                                        butonSalvare.addActionListener (new ActionListener()
                                        {
                                            @Override
                                            public void actionPerformed (ActionEvent e)
                                            {
                                            	String nume = campNume.getText();
                                            	String prenume = campPrenume.getText();
                                            	String cnp = campCNP.getText();
                                            	Character selectedSex = (Character) sex.getSelectedItem();
                                                char sexValue = selectedSex.charValue();
                                                int zi = (int)ziSpinner.getValue();
                                                int luna = (int)lunaSpinner.getValue();
                                                int an = (int)anSpinner.getValue();
                                                String strada = campStrada.getText();
                                                String nr = campNr.getText();
                                                String oras = campOras.getText();
                                                String judet = campJudet.getText();
                                                String tara = campTara.getText();
                                                
                                            	Pacient pacient = new Pacient(nume,prenume,cnp,sexValue,new Data(zi,luna,an),new Adresa(strada,nr,oras,judet,tara));
                                            	
                                        		pacienti.set(i[0], pacient);
                                        		
                                        		String continut = "";
                                        		for (int i = 0; i < pacienti.size() - 1; i++)
                                        			continut += pacienti.get(i).toText();
                                        		continut += pacienti.get(pacienti.size()-1).getNume() + " " + pacienti.get(pacienti.size()-1).getPrenume() + " " + pacienti.get(pacienti.size()-1).getCNP() + " " + pacienti.get(pacienti.size()-1).getSex() + " " + pacienti.get(pacienti.size()-1).getDataNasterii() + " " + pacienti.get(pacienti.size()-1).getAdresa();
                            					
                        	                    String path = "src/utilitare/pacienti.txt";
                        	                    try
                        				        {
                        				            FileWriter fileWriter = new FileWriter(path);
                        				            BufferedWriter bufferedWriter = new BufferedWriter(fileWriter);
                        				            bufferedWriter.write(continut);
                        				            bufferedWriter.close();
                        				        }
                        				        catch (IOException ex)
                        				        {
                        				            System.out.println("A intervenit o eroare");
                        				        }
                        	                    ecranDatePersonale.dispose();
                                            }
                                        });
                                        
                                        panel.add(labelNume);
                                        panel.add(campNume);
                                        panel.add(labelPrenume);
                                        panel.add(campPrenume);
                                        panel.add(labelCNP);
                                        panel.add(campCNP);
                                        panel.add(labelSex);
                                        panel.add(sex);
                                        panel.add(labelZi);
                                        panel.add(ziSpinner);
                                        panel.add(labelLuna);
                                        panel.add(lunaSpinner);
                                        panel.add(labelAn);
                                        panel.add(anSpinner);
                                        panel.add(labelStrada);
                                        panel.add(campStrada);
                                        panel.add(labelNr);
                                        panel.add(campNr);
                                        panel.add(labelOras);
                                        panel.add(campOras);
                                        panel.add(labelJudet);
                                        panel.add(campJudet);
                                        panel.add(labelTara);
                                        panel.add(campTara);
                                        panel.add(butonSalvare);
                                        
                                        ecranDatePersonale.add(panel);
                                        
                                        ecranDatePersonale.getContentPane().setBackground(Color.BLACK);
                                        panel.setBackground(Color.DARK_GRAY);
                                        panel.setForeground(Color.WHITE);
                                        labelNume.setForeground(Color.WHITE);
                                        labelPrenume.setForeground(Color.WHITE);
                                        labelCNP.setForeground(Color.WHITE);
                                        labelSex.setForeground(Color.WHITE);
                                        labelZi.setForeground(Color.WHITE);
                                        labelLuna.setForeground(Color.WHITE);
                                        labelAn.setForeground(Color.WHITE);
                                        labelStrada.setForeground(Color.WHITE);
                                        labelNr.setForeground(Color.WHITE);
                                        labelOras.setForeground(Color.WHITE);
                                        labelJudet.setForeground(Color.WHITE);
                                        labelTara.setForeground(Color.WHITE);
                                        butonSalvare.setBackground(Color.CYAN);
                                        butonSalvare.setForeground(Color.BLACK);
                                        
                                        ecranDatePersonale.setDefaultCloseOperation(JFrame.DISPOSE_ON_CLOSE);
                                        ecranDatePersonale.setVisible(true);
                                    }
                                });
                                
                                //STERGERE
                                deleteBtn.addActionListener (new ActionListener()
                                {
                                	@Override
                                    public void actionPerformed (ActionEvent e)
                                    {
                                		Pacient pacient = new Pacient();
                                    	final int[] i = {0};
                                    	for (Pacient p: pacienti)
                                    	{
                                    		String numeComplet = p.getNume().toUpperCase() + " " + p.getPrenume().toUpperCase();
                                    		if (numeComplet.equals(numePacient))
                                    		{
                                    			pacienti.remove(i[0]);
                                    			break;
                                    		}
                                    		i[0] ++;
                                    	}
                                    	
                                		String continut = "";
                                		for (Pacient p: pacienti)
                                			continut += p.toText();
                                		
                                		String path = "src/utilitare/pacienti.txt";
                                        try
                                        {
                                            FileWriter fileWriter = new FileWriter(path);
                                            BufferedWriter bufferedWriter = new BufferedWriter(fileWriter);
                                            bufferedWriter.write(continut);
                                            bufferedWriter.close();
                                        }
                                        catch (IOException ex)
                                        {
                                            System.out.println("A intervenit o eroare");
                                        }
                                    }
                                });
                                
                                //GENERARE CONSULTATIE
                                consultBtn.addActionListener (new ActionListener()
                                {
                                	@Override
                                    public void actionPerformed (ActionEvent e)
                                    {
                                		List <Pacient> pac = new ArrayList <Pacient> ();
                                		for (Pacient p: pacienti)
                                    	{
                                    		String numeComplet = p.getNume().toUpperCase() + " " + p.getPrenume().toUpperCase();
                                    		if (numeComplet.equals(numePacient))
                                    		{
                                    			pac.add(p);
                                    			break;
                                    		}
                                    	}
                                		
                                		JFrame ecranGenerare = new JFrame("Generare consultatie");
                                		ecranGenerare.setSize(800, 600);
                                		ecranGenerare.setExtendedState(JFrame.MAXIMIZED_BOTH);
                                        
                                		GridBagConstraints gbc = new GridBagConstraints();
                                		gbc.insets = new Insets(5, 5, 5, 5);
                                		JPanel panel = new JPanel(new GridBagLayout());
                                		
                                		ecranGenerare.setJMenuBar(meniu(mainFrame,pacienti,afisare,registru,campPacienti,campConsultatii,scrollPacienti,scrollConsultatii,fisierPacienti,fisierConsultatii));
                                        
                                        JLabel labelNume = new JLabel("Pacient: ");
                                        JLabel labelZi = new JLabel("Zi: ");
                                        JLabel labelLuna = new JLabel("Luna: ");
                                        JLabel labelAn = new JLabel("An: ");
                                        JLabel labelSimptome = new JLabel("Simptome: ");
                                        JLabel labelDiagostic = new JLabel("Diagnostic: ");
                                        JLabel labelBT = new JLabel("BT: ");
                                        JLabel labelBI = new JLabel("BI: ");
                                        JLabel labelRp = new JLabel("R/p: ");
                                        
                                        JComboBox <String> pacientiOptiuni = new JComboBox <String> ();
                        	            String numeComplet = pac.get(0).getNume().toUpperCase() + " " + pac.get(0).getPrenume().toUpperCase();
                        	            pacientiOptiuni.addItem(numeComplet);
                        	            pacientiOptiuni.setEditable(false);
                        	            
                        	            SpinnerModel ziSpinnerModel = new SpinnerNumberModel(LocalDate.now().getDayOfMonth(), 1, 31, 1);
                        	            JSpinner ziSpinner = new JSpinner(ziSpinnerModel);

                        	            SpinnerModel lunaSpinnerModel = new SpinnerNumberModel(LocalDate.now().getMonthValue(), 1, 12, 1);
                        	            JSpinner lunaSpinner = new JSpinner(lunaSpinnerModel);

                        	            int anCurent = Year.now().getValue();
                        	            SpinnerModel anSpinnerModel = new SpinnerNumberModel(anCurent, 1, anCurent, 1);
                        	            JSpinner anSpinner = new JSpinner(anSpinnerModel);
                                        
                        	            JTextArea campSimptome = new JTextArea();
                        	            campSimptome.setRows(8);
                        	            campSimptome.setColumns(30);
                        	            campSimptome.setPreferredSize(new Dimension(300, 200));
                        	            campSimptome.setLineWrap(true);
                        	            
                                        JTextField campDiagnostic = new JTextField();
                                        JTextField campBT = new JTextField();
                                        JTextField campBI = new JTextField();
                                        JTextField campRp = new JTextField();
                                        
                                        JButton butonSalvare = new JButton("Adauga");
                                        butonSalvare.addActionListener (new ActionListener()
                                        {
                                            @Override
                                            public void actionPerformed (ActionEvent e)
                                            {
                                                int zi = (int)ziSpinner.getValue();
                                                int luna = (int)lunaSpinner.getValue();
                                                int an = (int)anSpinner.getValue();
                                                
                                                List <String> simptome = new ArrayList <String> ();
                                                String[] randuri = campSimptome.getText().split("\n");
                                                for (String r: randuri)
                                                	simptome.add(r.trim());
                                                
                                                String diag = campDiagnostic.getText();
                                                String bt = campBT.getText();
                                                String bi = campBI.getText();
                                                String rp = campRp.getText();
                                                
                                            	Consultatie cons = new Consultatie(pac.get(0),new Data(zi,luna,an),simptome,diag,bt,bi,rp);
                        	                    registru.add(cons);
                        	                    String path = "consultatii.txt";
                        	        	        try
                        	        	        {
                        	        	            FileWriter fileWriter = new FileWriter(path, true);
                        	        	            BufferedWriter bufferedWriter = new BufferedWriter(fileWriter);
                        	        	            bufferedWriter.write("\n" + cons.toText());
                        	        	            bufferedWriter.close();
                        	        	        }
                        	        	        catch (IOException ex)
                        	        	        {
                        	        	            System.out.println("A intervenit o eroare!");
                        	        	        }
                        	                    ecranGenerare.dispose();
                                            }
                                        });
                                        
                                        JPanel utilitar = new JPanel();
                                        
                                        gbc.gridx = 0;
                                        gbc.gridy = 0;
                                        panel.add(labelNume, gbc);

                                        gbc.gridx = 1;
                                        gbc.gridy = 0;
                                        panel.add(pacientiOptiuni, gbc);

                                        gbc.gridx = 0;
                                        gbc.gridy = 1;
                                        panel.add(labelZi, gbc);

                                        gbc.gridx = 1;
                                        panel.add(ziSpinner, gbc);

                                        gbc.gridx = 2;
                                        gbc.gridy = 1;
                                        panel.add(labelLuna, gbc);

                                        gbc.gridx = 3;
                                        panel.add(lunaSpinner, gbc);

                                        gbc.gridx = 4;
                                        gbc.gridy = 1;
                                        panel.add(labelAn, gbc);

                                        gbc.gridx = 5;
                                        panel.add(anSpinner, gbc);

                                        gbc.gridx = 0;
                                        gbc.gridy = 2;
                                        panel.add(utilitar, gbc);

                                        gbc.gridx = 0;
                                        gbc.gridy = 3;
                                        panel.add(labelSimptome, gbc);

                                        gbc.gridx = 1;
                                        gbc.gridy = 4;
                                        gbc.gridwidth = 5;
                                        gbc.gridheight = 4;
                                        gbc.fill = GridBagConstraints.BOTH;
                                        panel.add(new JScrollPane(campSimptome), gbc);

                                        gbc.gridx = 0;
                                        gbc.gridy = 8;
                                        panel.add(labelDiagostic, gbc);

                                        gbc.gridx = 1;
                                        panel.add(campDiagnostic, gbc);

                                        gbc.gridx = 0;
                                        gbc.gridy = 9;
                                        gbc.gridheight = 4;
                                        panel.add(utilitar, gbc);

                                        gbc.gridx = 0;
                                        gbc.gridy = 13;
                                        panel.add(labelBT, gbc);

                                        gbc.gridx = 1;
                                        panel.add(campBT, gbc);

                                        gbc.gridx = 0;
                                        gbc.gridy = 15;
                                        gbc.gridheight = 2;
                                        panel.add(utilitar, gbc);

                                        gbc.gridx = 0;
                                        gbc.gridy = 17;
                                        panel.add(labelBI, gbc);

                                        gbc.gridx = 1;
                                        panel.add(campBI, gbc);

                                        gbc.gridx = 0;
                                        gbc.gridy = 19;
                                        panel.add(labelRp, gbc);

                                        gbc.gridx = 1;
                                        panel.add(campRp, gbc);

                                        gbc.gridx = 0;
                                        gbc.gridy = 21;
                                        gbc.gridheight = 2;
                                        panel.add(utilitar, gbc);

                                        gbc.gridx = 0;
                                        gbc.gridy = 23;
                                        panel.add(butonSalvare, gbc);
                                        
                                        ecranGenerare.add(panel);
                                        
                                        ecranGenerare.getContentPane().setBackground(Color.BLACK);
                                        panel.setBackground(Color.DARK_GRAY);
                                        panel.setForeground(Color.WHITE);
                                        utilitar.setBackground(Color.DARK_GRAY);
                                        utilitar.setForeground(Color.WHITE);
                                        labelNume.setForeground(Color.WHITE);
                                        labelZi.setForeground(Color.WHITE);
                                        labelLuna.setForeground(Color.WHITE);
                                        labelAn.setForeground(Color.WHITE);
                                        labelSimptome.setForeground(Color.WHITE);
                                        labelDiagostic.setForeground(Color.WHITE);
                                        labelBT.setForeground(Color.WHITE);
                                        labelBI.setForeground(Color.WHITE);
                                        labelRp.setForeground(Color.WHITE);
                                        butonSalvare.setBackground(Color.CYAN);
                                        butonSalvare.setForeground(Color.BLACK);
                                        
                                        ecranGenerare.setDefaultCloseOperation(JFrame.DISPOSE_ON_CLOSE);
                                        ecranGenerare.setVisible(true);
                                    }
                                });
                            }
                        }
                    }
                });

                GroupLayout layout = new GroupLayout(ecranCautare.getContentPane());
                ecranCautare.getContentPane().setLayout(layout);

                layout.setAutoCreateGaps(true);
                layout.setAutoCreateContainerGaps(true);

                GroupLayout.SequentialGroup hGroup = layout.createSequentialGroup();
                hGroup.addGroup(layout.createParallelGroup().addComponent(search).addComponent(rezultate));
                hGroup.addGroup(layout.createParallelGroup().addComponent(searchBtn).addComponent(campDetalii));
                hGroup.addGroup(layout.createParallelGroup().addComponent(cons).addComponent(campCons));
                hGroup.addGroup(layout.createParallelGroup().addComponent(label).addComponent(optiuni));
                layout.setHorizontalGroup(hGroup);

                GroupLayout.SequentialGroup vGroup = layout.createSequentialGroup();
                vGroup.addGroup(layout.createParallelGroup(GroupLayout.Alignment.BASELINE).addComponent(search).addComponent(searchBtn).addComponent(cons).addComponent(label));
                vGroup.addGroup(layout.createParallelGroup(GroupLayout.Alignment.BASELINE).addComponent(rezultate).addComponent(campDetalii).addComponent(campCons).addComponent(optiuni));
                layout.setVerticalGroup(vGroup);

                ecranCautare.getContentPane().setBackground(Color.BLACK);
                search.setBackground(Color.DARK_GRAY);
                search.setForeground(Color.WHITE);
                searchBtn.setBackground(Color.DARK_GRAY);
                searchBtn.setForeground(Color.WHITE);
                label.setForeground(Color.BLACK);
                cons.setForeground(Color.BLACK);
                rezultate.setBackground(Color.DARK_GRAY);
                rezultate.setForeground(Color.WHITE);
                optiuni.setBackground(Color.BLACK);
                optiuni.setForeground(Color.WHITE);
                deleteBtn.setBackground(Color.RED);
                campDetalii.setBackground(Color.DARK_GRAY);
                campDetalii.setForeground(Color.WHITE);
                campCons.setBackground(Color.DARK_GRAY);
                campCons.setForeground(Color.WHITE);

                ecranCautare.setDefaultCloseOperation(JFrame.DISPOSE_ON_CLOSE);
                ecranCautare.setVisible(true);
            }
        });
    }
    
    /*
     * Aceasta metoda defineste actiunea indeplinita de butonul "Adauga un pacient", si anume
     * posibilitatea introducerii de date pentru creerea unui nou pacient
     * @param adaugareMenu - butonul din meniu caruia i se adauga actiunea
     * @param mainFrame - fereastra principala a aplicatiei
     * @param pacienti - lista cu pacienti
     * @param afisare - lista cu pacienti care va fi afisata
     * @param registru - lista cu consultatii
     * @param campPacienti - campul in care sunt afisati pacientii
     * @param campConsultatii - campul in care sunt afisate consultatiile
     * @param scrollPacienti - scroll-ul ce cuprinde campul cu pacienti
     * @param scrollConsultatii - scroll-ul ce cuprinde campul cu consultatii
     * @param fisierPacienti - fisierul in care sunt stocati pacientii
     * @param fisierConsultatii - fisierul in care sunt stocate consultatiile
     */
    public static void adaugareAct (JMenuItem adaugareMenu, JFrame mainFrame, List <Pacient> pacienti, List <Pacient> afisare, List <Consultatie> registru, JTextArea campPacienti, JTextArea campConsultatii, JScrollPane scrollPacienti, JScrollPane scrollConsultatii, File fisierPacienti, File fisierConsultatii)
    {
    	adaugareMenu.addActionListener (new ActionListener()
        {
            @Override
            public void actionPerformed (ActionEvent e)
            {
                JFrame ecranAdaugare = new JFrame("Adaugare pacient");
                ecranAdaugare.setSize(800, 600);
                ecranAdaugare.setExtendedState(JFrame.MAXIMIZED_BOTH);
                
                ecranAdaugare.setJMenuBar(meniu(mainFrame,pacienti,afisare,registru,campPacienti,campConsultatii,scrollPacienti,scrollConsultatii,fisierPacienti,fisierConsultatii));
                
                JPanel panel = new JPanel();
                panel.setLayout(new GridLayout(14, 2, 10, 10));
                
                JLabel labelNume = new JLabel("Nume: ");
                JLabel labelPrenume = new JLabel("Prenume: ");
                JLabel labelCNP = new JLabel("CNP: ");
                JLabel labelSex = new JLabel("Sex: ");
                JLabel labelZi = new JLabel("Zi: ");
                JLabel labelLuna = new JLabel("Luna: ");
                JLabel labelAn = new JLabel("An: ");
                JLabel labelStrada = new JLabel("Strada: ");
                JLabel labelNr = new JLabel("Nr.: ");
                JLabel labelOras = new JLabel("Oras: ");
                JLabel labelJudet = new JLabel("Judet: ");
                JLabel labelTara = new JLabel("Tara: ");
                
                JTextField campNume = new JTextField();
                JTextField campPrenume = new JTextField();
                JTextField campCNP = new JTextField();
                
                char[] sexOptions = {'M', 'F'};
                JComboBox <Character> sex = new JComboBox<>();
                for (char option: sexOptions)
                    sex.addItem(option);
                sex.setEditable(false);
                
                SpinnerModel ziSpinnerModel = new SpinnerNumberModel(1, 1, 31, 1);
                JSpinner ziSpinner = new JSpinner(ziSpinnerModel);
                
                SpinnerModel lunaSpinnerModel = new SpinnerNumberModel(1, 1, 12, 1);
                JSpinner lunaSpinner = new JSpinner(lunaSpinnerModel);
                
                int anCurent = Year.now().getValue();
                SpinnerModel anSpinnerModel = new SpinnerNumberModel(anCurent, 1, anCurent, 1);
                JSpinner anSpinner = new JSpinner(anSpinnerModel);
                
                JTextField campStrada = new JTextField();
                JTextField campNr = new JTextField();
                JTextField campOras = new JTextField();
                JTextField campJudet = new JTextField();
                JTextField campTara = new JTextField();
                campTara.setText("Romania");
                
                JButton butonSalvare = new JButton("Adauga");
                butonSalvare.addActionListener (new ActionListener()
                {
                    @Override
                    public void actionPerformed (ActionEvent e)
                    {
                    	String nume = campNume.getText();
                    	String prenume = campPrenume.getText();
                    	String cnp = campCNP.getText();
                    	Character selectedSex = (Character) sex.getSelectedItem();
                        char sexValue = selectedSex.charValue();
                        int zi = (int)ziSpinner.getValue();
                        int luna = (int)lunaSpinner.getValue();
                        int an = (int)anSpinner.getValue();
                        String strada = campStrada.getText();
                        String nr = campNr.getText();
                        String oras = campOras.getText();
                        String judet = campJudet.getText();
                        String tara = campTara.getText();
                        
                    	Pacient pacient = new Pacient(nume,prenume,cnp,sexValue,new Data(zi,luna,an),new Adresa(strada,nr,oras,judet,tara));
	                    pacienti.add(pacient);
	                    String path = "src/utilitare/pacienti.txt";
	                    try
	                    {
	                    	FileWriter fileWriter = new FileWriter(path, true);
	                        BufferedWriter bufferedWriter = new BufferedWriter(fileWriter);
	                        bufferedWriter.write("\n" + pacient.toText());
	                        bufferedWriter.close();
	                    }
	                    catch (IOException ex)
	                    {
	                        System.out.println("A intervenit o eroare!");
	                    }
	                    ecranAdaugare.dispose();
                    }
                });
                
                panel.add(labelNume);
                panel.add(campNume);
                panel.add(labelPrenume);
                panel.add(campPrenume);
                panel.add(labelCNP);
                panel.add(campCNP);
                panel.add(labelSex);
                panel.add(sex);
                panel.add(labelZi);
                panel.add(ziSpinner);
                panel.add(labelLuna);
                panel.add(lunaSpinner);
                panel.add(labelAn);
                panel.add(anSpinner);
                panel.add(labelStrada);
                panel.add(campStrada);
                panel.add(labelNr);
                panel.add(campNr);
                panel.add(labelOras);
                panel.add(campOras);
                panel.add(labelJudet);
                panel.add(campJudet);
                panel.add(labelTara);
                panel.add(campTara);
                panel.add(butonSalvare);
                
                ecranAdaugare.add(panel);
                
                ecranAdaugare.getContentPane().setBackground(Color.BLACK);
                panel.setBackground(Color.DARK_GRAY);
                panel.setForeground(Color.WHITE);
                labelNume.setForeground(Color.WHITE);
                labelPrenume.setForeground(Color.WHITE);
                labelCNP.setForeground(Color.WHITE);
                labelSex.setForeground(Color.WHITE);
                labelZi.setForeground(Color.WHITE);
                labelLuna.setForeground(Color.WHITE);
                labelAn.setForeground(Color.WHITE);
                labelStrada.setForeground(Color.WHITE);
                labelNr.setForeground(Color.WHITE);
                labelOras.setForeground(Color.WHITE);
                labelJudet.setForeground(Color.WHITE);
                labelTara.setForeground(Color.WHITE);
                butonSalvare.setBackground(Color.CYAN);
                butonSalvare.setForeground(Color.BLACK);
                
                ecranAdaugare.setDefaultCloseOperation(JFrame.DISPOSE_ON_CLOSE);
                ecranAdaugare.setVisible(true);
            }
        });
    }
    
    
    /*
     * Aceasta metoda reprezinta bara de meniu a aplicatiei, vizibila din toate ferestrele ei componente
     * @param mainFrame - fereastra principala a aplicatiei
     * @param generareMenu - butonul pentru generarea unei consultatii
     * @param pacienti - lista cu pacienti
     * @param afisare - lista cu pacienti care va fi afisata
     * @param registru - lista cu consultatii
     * @param campPacienti - campul in care sunt afisati pacientii
     * @param campConsultatii - campul in care sunt afisate consultatiile
     * @param scrollPacienti - scroll-ul ce cuprinde campul cu pacienti
     * @param scrollConsultatii - scroll-ul ce cuprinde campul cu consultatii
     * @param fisierPacienti - fisierul in care sunt stocati pacientii
     * @param fisierConsultatii - fisierul in care sunt stocate consultatiile
     * @return bara de meniu
     */
    public static JMenuBar meniu (JFrame mainFrame, List <Pacient> pacienti, List <Pacient> afisare, List <Consultatie> registru, JTextArea campPacienti, JTextArea campConsultatii, JScrollPane scrollPacienti, JScrollPane scrollConsultatii, File fisierPacienti, File fisierConsultatii)
    {
    	JMenuBar meniu = new JMenuBar();
        mainFrame.setJMenuBar(meniu);

        JMenu opt = new JMenu("Home");
        JMenuItem home = new JMenuItem("Acasa");
        homeAct(home,pacienti,afisare,registru,fisierPacienti,fisierConsultatii);
        opt.add(home);
        
        JMenu pacientiMenu = new JMenu("Pacienti");
        JMenuItem cautareMenu = new JMenuItem("Cauta un pacient");
        JMenuItem adaugareMenu = new JMenuItem("Adauga un pacient");
        JMenu consultatiiMenu = new JMenu("Consultatii");
        JMenuItem generareMenu = new JMenuItem("Genereaza consultatie");
        
        cautareAct(cautareMenu,mainFrame,generareMenu,pacienti,afisare,registru,campPacienti,campConsultatii,scrollPacienti,scrollConsultatii,fisierPacienti,fisierConsultatii);
        pacientiMenu.add(cautareMenu);
        
        adaugareAct(adaugareMenu,mainFrame,pacienti,afisare,registru,campPacienti,campConsultatii,scrollPacienti,scrollConsultatii,fisierPacienti,fisierConsultatii);
        pacientiMenu.add(adaugareMenu);

        generareAct(generareMenu,mainFrame,pacienti,afisare,registru,campPacienti,campConsultatii,scrollPacienti,scrollConsultatii,fisierPacienti,fisierConsultatii);
        consultatiiMenu.add(generareMenu);
        
        meniu.add(opt);
        meniu.add(pacientiMenu);
        meniu.add(consultatiiMenu);
        
        return meniu;
    }
    
    public static void main (String[] args)
    {
    	JFrame mainFrame = new JFrame();
    	mainFrame.setTitle("EasyMed");
    	mainFrame.setSize(800, 600);
    	mainFrame.setExtendedState(JFrame.MAXIMIZED_BOTH);
    	mainFrame.setLayout(new GridLayout(0,2));
    	
    	JTextField search;
    	JButton searchBtn;
        JLabel etichetaPacienti;
        JLabel etichetaConsultatii;
        JTextArea campPacienti;
        JTextArea campConsultatii;
        JScrollPane scrollPacienti;
        JScrollPane scrollConsultatii;
        
        campPacienti = new JTextArea(100,200);
        campConsultatii = new JTextArea(100,200);
        scrollPacienti = new JScrollPane();
        scrollConsultatii = new JScrollPane();
        
        try
        {
            UIManager.setLookAndFeel("javax.swing.plaf.nimbus.NimbusLookAndFeel");
        }
        catch (Exception e)
        {
            e.printStackTrace();
        }
        
        //CITIRE
        List <Pacient> pacienti = new ArrayList <Pacient> ();
        List <Consultatie> registru = new ArrayList <Consultatie> ();
        File fisierPacienti = new File("src/utilitare/pacienti.txt");
        File fisierConsultatii = new File("src/utilitare/consultatii.txt");
        citire(fisierPacienti,fisierConsultatii,pacienti,registru);
        
        List <Pacient> afisare = new ArrayList <Pacient> ();
        afisare.addAll(pacienti);
        Collections.sort (afisare, new Comparator <Pacient> ()
        {
            @Override
            public int compare (Pacient p1, Pacient p2)
            {
                int rez = p1.getNume().compareToIgnoreCase(p2.getNume());
                if (rez == 0)
                    rez = p1.getPrenume().compareToIgnoreCase(p2.getPrenume());
                return rez;
            }
        });

        //MENIU
        meniu(mainFrame,pacienti,afisare,registru,campPacienti,campConsultatii,scrollPacienti,scrollConsultatii,fisierPacienti,fisierConsultatii);
        
        //AFISARE
        afisare(campPacienti,campConsultatii,afisare,registru);
        scrollPacienti.setViewportView(campPacienti);
        scrollConsultatii.setViewportView(campConsultatii);
        
        //FORMAT
        campPacienti.setBackground(Color.DARK_GRAY);
        campPacienti.setForeground(Color.WHITE);
        campConsultatii.setBackground(Color.DARK_GRAY);
        campConsultatii.setForeground(Color.WHITE);

        GroupLayout layout = new GroupLayout(mainFrame.getContentPane());
        mainFrame.getContentPane().setLayout(layout);

        layout.setAutoCreateGaps(true);
        layout.setAutoCreateContainerGaps(true);

        GroupLayout.SequentialGroup hGroup = layout.createSequentialGroup();
        hGroup.addGroup(layout.createParallelGroup().addComponent(scrollPacienti));
        hGroup.addGroup(layout.createParallelGroup().addComponent(scrollConsultatii));
        layout.setHorizontalGroup(hGroup);

        GroupLayout.SequentialGroup vGroup = layout.createSequentialGroup();
        vGroup.addGroup(layout.createParallelGroup(GroupLayout.Alignment.BASELINE).addComponent(scrollPacienti).addComponent(scrollConsultatii));
        layout.setVerticalGroup(vGroup);

        mainFrame.getContentPane().setBackground(Color.BLACK);
        mainFrame.setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
        mainFrame.setVisible(true);
    }
}

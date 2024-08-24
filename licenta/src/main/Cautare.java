package main;

import java.awt.Color;
import java.awt.Dimension;
import java.awt.GridBagConstraints;
import java.awt.GridBagLayout;
import java.awt.GridLayout;
import java.awt.Insets;
import java.awt.event.ActionEvent;
import java.awt.event.ActionListener;
import java.io.BufferedWriter;
import java.io.File;
import java.io.FileWriter;
import java.io.IOException;
import java.time.LocalDate;
import java.time.Year;
import java.util.ArrayList;
import java.util.List;

import javax.swing.BoxLayout;
import javax.swing.DefaultListModel;
import javax.swing.GroupLayout;
import javax.swing.JButton;
import javax.swing.JComboBox;
import javax.swing.JFrame;
import javax.swing.JLabel;
import javax.swing.JList;
import javax.swing.JMenuItem;
import javax.swing.JPanel;
import javax.swing.JScrollPane;
import javax.swing.JSpinner;
import javax.swing.JTextArea;
import javax.swing.JTextField;
import javax.swing.SpinnerModel;
import javax.swing.SpinnerNumberModel;
import javax.swing.event.ListSelectionEvent;
import javax.swing.event.ListSelectionListener;

import clase.Adresa;
import clase.Consultatie;
import clase.Data;
import clase.Pacient;

public class Cautare
{
	/**
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
    public static void cautareAct (JMenuItem cautareMenu, JMenuItem generareMenu, List <Pacient> pacienti, List <Pacient> afisare, List <Consultatie> registru, File fisierPacienti, File fisierConsultatii)
    {
    	cautareMenu.addActionListener (new ActionListener()
        {
            @Override
            public void actionPerformed (ActionEvent e)
            {
            	JFrame ecranCautare = new JFrame("Cautare...");
                ecranCautare.setSize(800, 600);
                ecranCautare.setExtendedState(JFrame.MAXIMIZED_BOTH);
                
                ecranCautare.setJMenuBar(GUI.meniu(GUI.mainFrame,pacienti,afisare,registru,fisierPacienti,fisierConsultatii));

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
                            	
                                campDetalii.setText(Citire.detaliiPacient(pacienti,numePacient));
                                campCons.setText(consultatii);
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
                                        
                                        ecranDatePersonale.setJMenuBar(GUI.meniu(GUI.mainFrame,pacienti,afisare,registru,fisierPacienti,fisierConsultatii));
                                        
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
                                        JLabel labelBloc = new JLabel("Bloc: ");
                                        JLabel labelScara = new JLabel("Scara: ");
                                        JLabel labelEtaj = new JLabel("Etaj: ");
                                        JLabel labelApart = new JLabel("Apartament: ");
                                        JLabel labelOras = new JLabel("Oras: ");
                                        JLabel labelJudet = new JLabel("Judet: ");
                                        
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
                                        
                                        JTextField campBloc = new JTextField();
                                        campBloc.setText(pacient.getAdresa().getBloc() + "");
                                        
                                        JTextField campScara = new JTextField();
                                        campScara.setText(pacient.getAdresa().getScara() + "");
                                        
                                        JTextField campEtaj = new JTextField();
                                        campEtaj.setText(pacient.getAdresa().getEtaj() + "");
                                        
                                        JTextField campApart = new JTextField();
                                        campApart.setText(pacient.getAdresa().getApart() + "");
                                        
                                        JTextField campOras = new JTextField();
                                        campOras.setText(pacient.getAdresa().getOras());
                                        
                                        JTextField campJudet = new JTextField();
                                        campJudet.setText(pacient.getAdresa().getJudet());
                                        
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
                                                String bloc = campBloc.getText();
                                                String scara = campScara.getText();
                                                String etaj = campEtaj.getText();
                                                String apart = campApart.getText();
                                                String oras = campOras.getText();
                                                String judet = campJudet.getText();
                                                
                                            	Pacient pacient = new Pacient(nume, prenume, cnp, sexValue, new Data(zi, luna, an), new Adresa(strada, nr, bloc, scara, etaj, apart, oras, judet));
                                            	
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
                                        panel.add(labelBloc);
                                        panel.add(campBloc);
                                        panel.add(labelScara);
                                        panel.add(campScara);
                                        panel.add(labelEtaj);
                                        panel.add(campEtaj);
                                        panel.add(labelApart);
                                        panel.add(campApart);
                                        panel.add(labelOras);
                                        panel.add(campOras);
                                        panel.add(labelJudet);
                                        panel.add(campJudet);
                                        panel.add(butonSalvare);
                                        
                                        ecranDatePersonale.add(panel);
                                        
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
                                		
                                		ecranGenerare.setJMenuBar(GUI.meniu(GUI.mainFrame,pacienti,afisare,registru,fisierPacienti,fisierConsultatii));
                                        
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

                deleteBtn.setBackground(Color.RED);

                ecranCautare.setDefaultCloseOperation(JFrame.DISPOSE_ON_CLOSE);
                ecranCautare.setVisible(true);
            }
        });
    }
}

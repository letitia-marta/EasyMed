package main;

import java.awt.Color;
import java.awt.Dimension;
import java.awt.GridBagConstraints;
import java.awt.GridBagLayout;
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

import javax.swing.JButton;
import javax.swing.JComboBox;
import javax.swing.JFrame;
import javax.swing.JLabel;
import javax.swing.JMenuItem;
import javax.swing.JPanel;
import javax.swing.JScrollPane;
import javax.swing.JSpinner;
import javax.swing.JTextArea;
import javax.swing.JTextField;
import javax.swing.SpinnerModel;
import javax.swing.SpinnerNumberModel;

import clase.Consultatie;
import clase.Data;
import clase.Pacient;

public class Generare
{
	/**
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
    public static void generareAct (JMenuItem generareMenu, List <Pacient> pacienti, List <Pacient> afisare, List <Consultatie> registru, File fisierPacienti, File fisierConsultatii)
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
                
                butonSalvare.setBackground(Color.CYAN);
                butonSalvare.setForeground(Color.BLACK);
                
                ecranGenerare.setDefaultCloseOperation(JFrame.DISPOSE_ON_CLOSE);
                ecranGenerare.setVisible(true);
            }
        });
    }
}

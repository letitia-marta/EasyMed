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
import java.util.List;

import javax.swing.JButton;
import javax.swing.JComboBox;
import javax.swing.JFrame;
import javax.swing.JLabel;
import javax.swing.JMenuItem;
import javax.swing.JPanel;
import javax.swing.JSpinner;
import javax.swing.JTextField;
import javax.swing.SpinnerModel;
import javax.swing.SpinnerNumberModel;

import clase.Adresa;
import clase.Consultatie;
import clase.Data;
import clase.Pacient;

public class Adaugare
{
	/**
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
    public static void adaugareAct (JMenuItem adaugareMenu, List<Pacient> pacienti, List<Pacient> afisare, List<Consultatie> registru, File fisierPacienti, File fisierConsultatii)
    {
	    adaugareMenu.addActionListener (new ActionListener()
	    {
	        @Override
	        public void actionPerformed(ActionEvent e) {
	            JFrame ecranAdaugare = new JFrame("Adaugare pacient");
	            ecranAdaugare.setSize(800, 600);
	            ecranAdaugare.setExtendedState(JFrame.MAXIMIZED_BOTH);
	
	            GridBagConstraints gbc = new GridBagConstraints();
	            gbc.insets = new Insets(5, 5, 5, 5);
	            gbc.fill = GridBagConstraints.HORIZONTAL;
	            JPanel panel = new JPanel(new GridBagLayout());
	
	            ecranAdaugare.setJMenuBar(GUI.meniu(GUI.mainFrame, pacienti, afisare, registru, fisierPacienti, fisierConsultatii));
	
	            JLabel labelNume = new JLabel("Nume: ");
	            JLabel labelPrenume = new JLabel("Prenume: ");
	            JLabel labelCNP = new JLabel("CNP: ");
	            JLabel labelSex = new JLabel("Sex: ");
	            JLabel labelStrada = new JLabel("Strada: ");
	            JLabel labelNr = new JLabel("Nr.: ");
	            JLabel labelBloc = new JLabel("Bloc: ");
	            JLabel labelScara = new JLabel("Scara: ");
	            JLabel labelEtaj = new JLabel("Etaj: ");
	            JLabel labelApart = new JLabel("Apartament: ");
	            JLabel labelOras = new JLabel("Oras: ");
	            JLabel labelJudet = new JLabel("Judet: ");
	
	            JTextField campNume = new JTextField();
	            JTextField campPrenume = new JTextField();
	            JTextField campCNP = new JTextField();
	            JTextField campStrada = new JTextField();
	            JTextField campNr = new JTextField();
	            JTextField campBloc = new JTextField();
	            JTextField campScara = new JTextField();
	            JTextField campEtaj = new JTextField();
	            JTextField campApart = new JTextField();
	            JTextField campOras = new JTextField();
	
	            char[] sexOptions = {'M', 'F'};
	            JComboBox <Character> sex = new JComboBox<>();
	            for (char option : sexOptions)
	                sex.addItem(option);
	            sex.setEditable(false);
	
	            LocalDate currentDate = LocalDate.now();
	            int currentDay = currentDate.getDayOfMonth();
	            int currentMonth = currentDate.getMonthValue();
	            int currentYear = currentDate.getYear();
	
	            SpinnerModel ziSpinnerModel = new SpinnerNumberModel(currentDay, 1, 31, 1);
	            JSpinner ziSpinner = new JSpinner(ziSpinnerModel);
	
	            SpinnerModel lunaSpinnerModel = new SpinnerNumberModel(currentMonth, 1, 12, 1);
	            JSpinner lunaSpinner = new JSpinner(lunaSpinnerModel);
	
	            SpinnerModel anSpinnerModel = new SpinnerNumberModel(currentYear, 1, currentYear, 1);
	            JSpinner anSpinner = new JSpinner(anSpinnerModel);
	
	            String[] judete = {"Alba", "Arad", "Arges", "Bacau", "Bihor", "Bistrita-Nasaud", "Botosani", "Brasov",
	                    "Braila", "Bucuresti", "Buzau", "Caras-Severin", "Calarasi", "Cluj", "Constanta", "Covasna",
	                    "Dambovita", "Dolj", "Galati", "Giurgiu", "Gorj", "Harghita", "Hunedoara", "Ialomita", "Iasi",
	                    "Ilfov", "Maramures", "Mehedinti", "Mures", "Neamt", "Olt", "Prahova", "Satu Mare", "Salaj",
	                    "Sibiu", "Suceava", "Teleorman", "Timis", "Tulcea", "Vaslui", "Valcea", "Vrancea"};
	            JComboBox <String> judetComboBox = new JComboBox<>(judete);
	            judetComboBox.setEditable(false);
	
	            JButton butonSalvare = new JButton("Adauga");
	            butonSalvare.addActionListener(new ActionListener() {
	                @Override
	                public void actionPerformed(ActionEvent e) {
	                    String nume = campNume.getText();
	                    String prenume = campPrenume.getText();
	                    String cnp = campCNP.getText();
	                    Character selectedSex = (Character) sex.getSelectedItem();
	                    char sexValue = selectedSex.charValue();
	                    int zi = (int) ziSpinner.getValue();
	                    int luna = (int) lunaSpinner.getValue();
	                    int an = (int) anSpinner.getValue();
	                    String strada = campStrada.getText();
	                    String nr = campNr.getText();
	                    String bloc = campBloc.getText();
	                    String scara = campScara.getText();
	                    String etaj = campEtaj.getText();
	                    String apart = campApart.getText();
	                    String oras = campOras.getText();
	                    String judet = (String) judetComboBox.getSelectedItem();
	
	                    Pacient pacient = new Pacient(nume, prenume, cnp, sexValue, new Data(zi, luna, an), new Adresa(strada, nr, bloc, scara, etaj, apart, oras, judet));
	                    pacienti.add(pacient);
	                    String path = "src/utilitare/pacienti.txt";
	                    try {
	                        FileWriter fileWriter = new FileWriter(path, true);
	                        BufferedWriter bufferedWriter = new BufferedWriter(fileWriter);
	                        bufferedWriter.write("\n" + pacient.toText());
	                        bufferedWriter.close();
	                    } catch (IOException ex) {
	                        System.out.println("A intervenit o eroare!");
	                    }
	                    ecranAdaugare.dispose();
	                }
	            });
	            
	            ziSpinner.setPreferredSize(new Dimension(5, ziSpinner.getPreferredSize().height));
	            lunaSpinner.setPreferredSize(new Dimension(5, lunaSpinner.getPreferredSize().height));
	            anSpinner.setPreferredSize(new Dimension(5, anSpinner.getPreferredSize().height));
	
	            gbc.insets = new Insets(5, 5, 5, 5);
	            
	            //rand 0
	            gbc.gridx = 1;
	            gbc.gridy = 0;
	            panel.add(labelNume, gbc);
	
	            gbc.gridx = 2;
	            gbc.gridwidth = 3;
	            panel.add(campNume, gbc);
	            gbc.gridwidth = 1;
	
	            gbc.gridx = 6;
	            gbc.gridy = 0;
	            panel.add(labelPrenume, gbc);
	
	            gbc.gridx = 7;
	            gbc.gridwidth = 3;
	            panel.add(campPrenume, gbc);
	            gbc.gridwidth = 1;
	
	            //rand 1
	            gbc.gridx = 1;
	            gbc.gridy = 1;
	            gbc.weightx = 1.0;
	            panel.add(labelCNP, gbc);
	
	            gbc.gridx = 2;
	            gbc.gridwidth = 3;
	            panel.add(campCNP, gbc);
	            gbc.gridwidth = 1;
	
	            gbc.gridx = 6;
	            gbc.gridy = 1;
	            panel.add(labelSex, gbc);
	
	            gbc.gridx = 7;
	            gbc.gridwidth = 3;
	            panel.add(sex, gbc);
	            gbc.gridwidth = 1;
	            
	            //rand 2
	            gbc.gridx = 1;
	            gbc.gridy = 2;
	            panel.add(new JLabel("Data nașterii:"), gbc);

	            gbc.gridx = 3;
	            panel.add(ziSpinner, gbc);

	            gbc.gridx = 6;
	            panel.add(lunaSpinner, gbc);

	            gbc.gridx = 9;
	            //gbc.gridwidth = 2;
	            panel.add(anSpinner, gbc);
	            
	            //rand 3
	            gbc.gridx = 1;
	            gbc.gridy = 3;
	            panel.add(labelStrada, gbc);

	            gbc.gridx = 3;
	            panel.add(campStrada, gbc);

	            gbc.gridx = 4;
	            gbc.gridy = 3;
	            panel.add(labelNr, gbc);

	            gbc.gridx = 6;
	            panel.add(campNr, gbc);
	            
	            gbc.gridx = 8;
	            gbc.gridy = 3;
	            panel.add(labelBloc, gbc);

	            gbc.gridx = 9;
	            panel.add(campBloc, gbc);
	            
	            //rand 4
	            gbc.gridx = 1;
	            gbc.gridy = 4;
	            panel.add(labelScara, gbc);

	            gbc.gridx = 3;
	            gbc.weightx = 1.0;
	            panel.add(campScara, gbc);
	            
	            gbc.gridx = 4;
	            gbc.gridy = 4;
	            gbc.weightx = 1.0;
	            panel.add(labelEtaj, gbc);

	            gbc.gridx = 6;
	            panel.add(campEtaj, gbc);
	            
	            gbc.gridx = 8;
	            gbc.gridy = 4;
	            gbc.weightx = 1.0;
	            panel.add(labelApart, gbc);

	            gbc.gridx = 9;
	            panel.add(campApart, gbc);

	            //rand 5
	            gbc.gridx = 1;
	            gbc.gridy = 5;
	            gbc.weightx = 1.0;
	            panel.add(labelOras, gbc);

	            gbc.gridx = 2;
	            gbc.weightx = 1.0;
	            gbc.gridwidth = 3;
	            panel.add(campOras, gbc);
	            gbc.gridwidth = 1;

	            gbc.gridx = 6;
	            gbc.gridy = 5;
	            gbc.weightx = 1.0;
	            panel.add(labelJudet, gbc);

	            gbc.gridx = 7;
	            gbc.weightx = 1.0;
	            gbc.gridwidth = 3;
	            panel.add(judetComboBox, gbc);
	            gbc.gridwidth = 1;

	            //rand 6
	            gbc.gridx = 9;
	            gbc.gridy = 6;
	            panel.add(butonSalvare, gbc);

	            ecranAdaugare.add(panel);
	
	            butonSalvare.setBackground(Color.CYAN);
	            butonSalvare.setForeground(Color.BLACK);
	
	            ecranAdaugare.setDefaultCloseOperation(JFrame.DISPOSE_ON_CLOSE);
	            ecranAdaugare.setVisible(true);
	        }
	    });
	}
}

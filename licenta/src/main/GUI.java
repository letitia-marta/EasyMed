package main;

import java.util.Collections;
import java.util.Comparator;
import javax.swing.*;

import clase.Consultatie;
import clase.Pacient;

import java.awt.*;
import java.awt.event.ActionEvent;
import java.awt.event.ActionListener;
import java.io.File;
import java.util.ArrayList;
import java.util.List;

public class GUI
{
	static JFrame mainFrame = new JFrame();
	static JTextField search;
	static JButton searchBtn;
	static JLabel etichetaPacienti = new JLabel("Pacienti");
	static JLabel etichetaConsultatii = new JLabel("Registru consultatii");
	static JTextArea campPacienti = new JTextArea(100, 200);
	static JTextArea campConsultatii = new JTextArea(100, 200);
	static JScrollPane scrollPacienti = new JScrollPane();
	static JScrollPane scrollConsultatii = new JScrollPane();
	static ImageIcon icon = new ImageIcon("src/utilitare/icon.png");
    static ImageIcon userIcon = new ImageIcon("src/utilitare/user.png");
	
    /**
     * Aceasta metoda umple cu informatii campurile pentru pacienti, respectiv consultatii
     * @param campPacienti - campul unde se afiseaza, sub forma de lista, pacientii
     * @param campConsultatii - campul unde se afiseaza datele despre consultatii
     * @param afisare - lista de pacienti care va fi afisata
     * @param registru - lista cu consultatiile
     */
    public static void afisare (List <Pacient> afisare, List <Consultatie> registru)
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

    /**
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
        		Citire.citire(fisierPacienti,fisierConsultatii,pacienti,registru);
        		
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
                meniu(newFrame,pacienti,afisare,registru,fisierPacienti,fisierConsultatii);
                
                //AFISARE
                afisare(afisare,registru);
                scrollPacienti.setViewportView(campPacienti);
                scrollConsultatii.setViewportView(campConsultatii);

                GroupLayout layout = new GroupLayout(newFrame.getContentPane());
                newFrame.getContentPane().setLayout(layout);

                layout.setAutoCreateGaps(true);
                layout.setAutoCreateContainerGaps(true);

                // Horizontal Group
                GroupLayout.SequentialGroup hGroup = layout.createSequentialGroup();
                hGroup.addGroup(layout.createParallelGroup()
                        .addComponent(etichetaPacienti)
                        .addComponent(scrollPacienti));
                hGroup.addGroup(layout.createParallelGroup()
                        .addComponent(etichetaConsultatii)
                        .addComponent(scrollConsultatii));
                layout.setHorizontalGroup(hGroup);

                // Vertical Group
                GroupLayout.SequentialGroup vGroup = layout.createSequentialGroup();
                vGroup.addGroup(layout.createParallelGroup(GroupLayout.Alignment.BASELINE)
                        .addComponent(etichetaPacienti)
                        .addComponent(etichetaConsultatii));
                vGroup.addGroup(layout.createParallelGroup(GroupLayout.Alignment.BASELINE)
                        .addComponent(scrollPacienti)
                        .addComponent(scrollConsultatii));
                layout.setVerticalGroup(vGroup);

                newFrame.setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
                newFrame.setVisible(true);
            }
        });
    }

    /**
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
    public static JMenuBar meniu (JFrame mainFrame, List<Pacient> pacienti, List<Pacient> afisare, List<Consultatie> registru, File fisierPacienti, File fisierConsultatii)
    {
        JMenuBar meniu = new JMenuBar();
        mainFrame.setJMenuBar(meniu);
        meniu.setPreferredSize(new Dimension(1000, 40));

        JMenu opt = new JMenu("Home");
        JMenuItem home = new JMenuItem("Acasa");
        homeAct(home, pacienti, afisare, registru, fisierPacienti, fisierConsultatii);
        opt.add(home);

        JMenu pacientiMenu = new JMenu("Pacienti");
        JMenuItem cautareMenu = new JMenuItem("Cauta un pacient");
        JMenuItem adaugareMenu = new JMenuItem("Adauga un pacient");
        JMenu consultatiiMenu = new JMenu("Consultatii");
        JMenuItem generareMenu = new JMenuItem("Genereaza consultatie");

        Cautare.cautareAct(cautareMenu, generareMenu, pacienti, afisare, registru, fisierPacienti, fisierConsultatii);
        pacientiMenu.add(cautareMenu);

        Adaugare.adaugareAct(adaugareMenu, pacienti, afisare, registru, fisierPacienti, fisierConsultatii);
        pacientiMenu.add(adaugareMenu);

        Generare.generareAct(generareMenu, pacienti, afisare, registru, fisierPacienti, fisierConsultatii);
        consultatiiMenu.add(generareMenu);

	    Image userImage = userIcon.getImage().getScaledInstance(20, 20, Image.SCALE_SMOOTH);
	    userIcon = new ImageIcon(userImage);
	    JMenu profileMenu = new JMenu();
	    profileMenu.setIcon(userIcon);
	    
	    JMenuItem profileItem = new JMenuItem("Profilul meu");
	    profileItem.addActionListener(e ->
	    {
	        JOptionPane.showMessageDialog(mainFrame, "Profile Clicked!", "Profile", JOptionPane.INFORMATION_MESSAGE);
	    });
	    profileMenu.add(profileItem);
	    
	    JMenuItem logOutItem = new JMenuItem("Deconectare");
	    logOutItem.addActionListener(e ->
	    {
	        JOptionPane.showMessageDialog(mainFrame, "V-ati deconectat cu succes!", "Profile", JOptionPane.INFORMATION_MESSAGE);
	    });
	    profileMenu.add(logOutItem);
	    
	    meniu.add(opt);
	    meniu.add(pacientiMenu);
	    meniu.add(consultatiiMenu);
	    meniu.add(Box.createHorizontalGlue());
	    meniu.add(profileMenu);
	
	    return meniu;
    }

    public static void main (String[] args)
    {
    	mainFrame.setTitle("EasyMed");
    	mainFrame.setSize(800, 600);
    	mainFrame.setExtendedState(JFrame.MAXIMIZED_BOTH);
    	mainFrame.setLayout(new GridLayout(0,2));
    	mainFrame.setIconImage(icon.getImage());
        
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
        Citire.citire(fisierPacienti,fisierConsultatii,pacienti,registru);
        
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
        meniu(mainFrame,pacienti,afisare,registru,fisierPacienti,fisierConsultatii);
        
        //AFISARE
        afisare(afisare,registru);
        scrollPacienti.setViewportView(campPacienti);
        scrollConsultatii.setViewportView(campConsultatii);

        GroupLayout layout = new GroupLayout(mainFrame.getContentPane());
        mainFrame.getContentPane().setLayout(layout);

        layout.setAutoCreateGaps(true);
        layout.setAutoCreateContainerGaps(true);

        // Horizontal Group
        GroupLayout.SequentialGroup hGroup = layout.createSequentialGroup();
        hGroup.addGroup(layout.createParallelGroup()
                .addComponent(etichetaPacienti)
                .addComponent(scrollPacienti));
        hGroup.addGroup(layout.createParallelGroup()
                .addComponent(etichetaConsultatii)
                .addComponent(scrollConsultatii));
        layout.setHorizontalGroup(hGroup);

        // Vertical Group
        GroupLayout.SequentialGroup vGroup = layout.createSequentialGroup();
        vGroup.addGroup(layout.createParallelGroup(GroupLayout.Alignment.BASELINE)
                .addComponent(etichetaPacienti)
                .addComponent(etichetaConsultatii));
        vGroup.addGroup(layout.createParallelGroup(GroupLayout.Alignment.BASELINE)
                .addComponent(scrollPacienti)
                .addComponent(scrollConsultatii));
        layout.setVerticalGroup(vGroup);

        mainFrame.setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
        mainFrame.setVisible(true);
    }
}

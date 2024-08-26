package main;

import java.util.ArrayList;
import java.util.List;

import javax.swing.DefaultComboBoxModel;

import clase.Pacient;

class PaginatedComboBoxModel extends DefaultComboBoxModel <String>
{
    private List<Pacient> originalList;
    private List<Pacient> filteredList;
    private int page;
    private final int PAGE_SIZE = 10;

    public PaginatedComboBoxModel (List<Pacient> pacienti)
    {
        this.originalList = pacienti;
        this.filteredList = new ArrayList<>(pacienti);
        this.page = 0;
        updateModel();
    }

    public void filter (String query)
    {
        if (query.isEmpty())
            filteredList = new ArrayList<>(originalList);
        else
        {
            filteredList = new ArrayList<>();
            for (Pacient p : originalList)
                if (p.toString().toLowerCase().contains(query.toLowerCase()))
                    filteredList.add(p);
        }
        page = 0;
        updateModel();
    }

    public void nextPage()
    {
        if (hasNextPage())
        {
            page++;
            updateModel();
        }
    }

    public void previousPage()
    {
        if (hasPreviousPage())
        {
            page--;
            updateModel();
        }
    }

    public boolean hasNextPage()
    {
        return (page + 1) * PAGE_SIZE < filteredList.size();
    }

    public boolean hasPreviousPage()
    {
        return page > 0;
    }

    private void updateModel()
    {
        removeAllElements();
        int start = page * PAGE_SIZE;
        int end = Math.min(start + PAGE_SIZE, filteredList.size());
        for (int i = start; i < end; i++) {
            addElement(filteredList.get(i).toString());
        }
    }
}

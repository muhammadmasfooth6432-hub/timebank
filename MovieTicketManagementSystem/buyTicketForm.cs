using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Data;
using System.Drawing;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Windows.Forms;
using System.Data.SqlClient;

namespace MovieTicketManagementSystem
{
    public partial class buyTicketForm : UserControl
    {
        string conn = @"Data Source=DESKTOP-S9J7TU2\SQLEXPRESS;Initial Catalog=movie;Integrated Security=True;";
        List<int> availableSeats = new List<int>();
        List<int> bookedSeatsForReceipt = new List<int>();
        double getTotal = 0;
        double getAmount = 0;
        double getChange = 0;
        string movie_id = "";
        int movie_price = 0;

        public buyTicketForm()
        {
            InitializeComponent();

            displayAvailableMovies();
            
        }



        public void refreshData()
        {
            if (InvokeRequired)
            {
                Invoke((MethodInvoker)refreshData);
                return;
            }

            displayAvailableMovies();
            
        }


        public void displayAvailableMovies()
        {

            movieData mData = new movieData();
            List<movieData> listData = mData.movieAvailableListData();
            dataGridView1.DataSource = listData;
        }

       
        private void dataGridView1_CellClick(object sender, DataGridViewCellEventArgs e)
        {

            if (e.RowIndex >= 0)
            {
                DataGridViewRow row = dataGridView1.Rows[e.RowIndex];
                movie_id = row.Cells[1].Value.ToString();
                labelMovieID.Text = movie_id;
                labelMovieName.Text = row.Cells[2].Value.ToString();
                labelGenre.Text = row.Cells[3].Value.ToString();
                movie_price = Convert.ToInt32(row.Cells[4].Value);
                labelRegularPrice.Text = movie_price.ToString("0.00");
                pictureBox1.ImageLocation = row.Cells[7].Value.ToString();

                displayAvailableSeats();
            }
        }

    

    

        public void displayAvailableSeats()
        {
       
            availableSeats.Clear();
            checkedListBoxAvailableSeats.Items.Clear();

            using (SqlConnection connect = new SqlConnection(conn))
            {
                connect.Open();

                SqlCommand getCapCmd = new SqlCommand("SELECT capacity FROM movies WHERE movies_id = @id", connect);
                getCapCmd.Parameters.AddWithValue("@id", movie_id);
                int capacity = Convert.ToInt32(getCapCmd.ExecuteScalar());

                SqlCommand getBookedCmd = new SqlCommand("SELECT seat_number FROM buy_tickets WHERE movie_id = @id", connect);
                getBookedCmd.Parameters.AddWithValue("@id", movie_id);
                SqlDataReader reader = getBookedCmd.ExecuteReader();

                List<int> booked = new List<int>();
                while (reader.Read())
                {
                    booked.Add(Convert.ToInt32(reader["seat_number"]));
                }
                reader.Close();

                availableSeats = Enumerable.Range(1, capacity).Except(booked).ToList();
                foreach (int seat in availableSeats)
                {
                    checkedListBoxAvailableSeats.Items.Add(seat);
                }
                labelAvailableSeat.Text =  availableSeats.Count.ToString();
            }

        }


        private void buttonCalculate_Click(object sender, EventArgs e)
        {
           
            int qty = checkedListBoxAvailableSeats.CheckedItems.Count;
            if (qty <= 0)
            {
                MessageBox.Show("Please select at least one seat.");
                return;
            }

            if (textBoxNumberofSeat.Text != checkedListBoxAvailableSeats.CheckedItems.Count.ToString())
            {
                MessageBox.Show("Number of selected seats does not match the entered quantity.");
                return;
            }

            double food = comboBoxFoods.SelectedIndex == -1 ? 0 : 100;
            double drink = comboBoxDrinks.SelectedIndex == -1 ? 0 : 50;

            getTotal = (movie_price * qty) + food + drink;
            labelTotalPrice.Text = "$" + getTotal.ToString("0.00");
        }


        public void ClearSelected()
        {
            labelAvailableSeat.Text = "";
            labelMovieID.Text = "";
            labelMovieName.Text = "";
            labelGenre.Text = "";
            labelRegularPrice.Text = "";
            checkedListBoxAvailableSeats.Items.Clear();
            pictureBox1.Image = null;
        }
        private void buttonSM_Clear_Click(object sender, EventArgs e)
        {
            ClearSelected();
        }

        private void textBoxAmount_KeyDown(object sender, KeyEventArgs e)
        {

            if (e.KeyCode == Keys.Enter)
            {
                if (!double.TryParse(textBoxAmount.Text, out getAmount))
                {
                    MessageBox.Show("Enter a valid number.");
                    return;
                }
                if (getAmount < getTotal)
                {
                    MessageBox.Show("Insufficient amount.");
                    return;
                }
                getChange = getAmount - getTotal;
                labelChange.Text = "$" + getChange.ToString("0.00");
            }
        }

        private void buttonBuy_Click(object sender, EventArgs e)
        {

            int qty = checkedListBoxAvailableSeats.CheckedItems.Count;
            if (qty <= 0)
            {
                MessageBox.Show("Please select seats to book.");
                return;
            }

            if (textBoxNumberofSeat.Text != checkedListBoxAvailableSeats.CheckedItems.Count.ToString())
            {
                MessageBox.Show("Number of selected seats does not match the entered quantity.");
                return;
            }

            bookedSeatsForReceipt.Clear(); // Clear old entries

            foreach (int seat in checkedListBoxAvailableSeats.CheckedItems)
            {
                bookedSeatsForReceipt.Add(seat);
            }

            using (SqlConnection connect = new SqlConnection(conn))
            {
                connect.Open();
                foreach (int seat in checkedListBoxAvailableSeats.CheckedItems)
                {
                    SqlCommand cmd = new SqlCommand("INSERT INTO buy_tickets(movie_id,seat_number,price,amount,change,status,created_at) VALUES(@movieID,@seat,@price,@amount,@change,@status,@date)", connect);
                    cmd.Parameters.AddWithValue("@movieID", movie_id);
                    cmd.Parameters.AddWithValue("@seat", seat);
                    cmd.Parameters.AddWithValue("@price", movie_price);
                    cmd.Parameters.AddWithValue("@amount", getAmount);
                    cmd.Parameters.AddWithValue("@change", getChange);
                    cmd.Parameters.AddWithValue("@status", "PAID");
                    cmd.Parameters.AddWithValue("@date", DateTime.Now);
                    cmd.ExecuteNonQuery();
                }
            }

            MessageBox.Show("Tickets booked successfully!");
            displayAvailableSeats();
        }


        public void clearFields()
        {
         
            comboBoxFoods.SelectedIndex = -1;
            comboBoxDrinks.SelectedIndex = -1;
            labelTotalPrice.Text = "$0.00";
            textBoxAmount.Text = "0.00";
            labelChange.Text = "$0.00";
            checkedListBoxAvailableSeats.Items.Clear();
            textBoxNumberofSeat.Text = "";
        }
        private void buttonClearFields_Click(object sender, EventArgs e)
        {
            clearFields();

        }

        private void buttonReceipt_Click(object sender, EventArgs e)
        {
            printDocument1.PrintPage += new System.Drawing.Printing.PrintPageEventHandler(printDocument1_PrintPage);
            printDocument1.BeginPrint += new System.Drawing.Printing.PrintEventHandler(printDocument1_BeginPrint);

            printPreviewDialog1.Document = printDocument1;
            printPreviewDialog1.ShowDialog();

        }

       

    
        private void printDocument1_PrintPage(object sender, System.Drawing.Printing.PrintPageEventArgs e)
        {

            float y = e.MarginBounds.Top;
            float x = e.MarginBounds.Left;
            int spacing = 10;

            Font titleFont = new Font("Segoe UI", 18, FontStyle.Bold);
            Font labelFont = new Font("Segoe UI", 12, FontStyle.Bold);
            Font valueFont = new Font("Segoe UI", 12);
            Font italicFont = new Font("Segoe UI", 11, FontStyle.Italic);

            // 🎭 Theater Name
            e.Graphics.DrawString("🎭 Archana Movie Theater", titleFont, Brushes.Black, x, y);
            y += titleFont.GetHeight(e.Graphics) + spacing * 2;

            // 🎬 Movie Name
            e.Graphics.DrawString("🎬 Movie:", labelFont, Brushes.Black, x, y);
            e.Graphics.DrawString(labelMovieName.Text, valueFont, Brushes.DarkBlue, x + 100, y);
            y += valueFont.GetHeight(e.Graphics) + spacing;

            // 🎟️ Seat Numbers
            string seats = bookedSeatsForReceipt.Count > 0 ? string.Join(", ", bookedSeatsForReceipt) : "None";
            e.Graphics.DrawString("🎟️ Seats:", labelFont, Brushes.Black, x, y);
            e.Graphics.DrawString(seats, valueFont, Brushes.DarkBlue, x + 100, y);
            y += valueFont.GetHeight(e.Graphics) + spacing;

            // 💰 Prices
            e.Graphics.DrawString("💰 Total:", labelFont, Brushes.Black, x, y);
            e.Graphics.DrawString($"${getTotal:0.00}", valueFont, Brushes.DarkBlue, x + 100, y);
            y += valueFont.GetHeight(e.Graphics);

            e.Graphics.DrawString("Paid:", labelFont, Brushes.Black, x, y);
            e.Graphics.DrawString($"${getAmount:0.00}", valueFont, Brushes.DarkBlue, x + 100, y);
            y += valueFont.GetHeight(e.Graphics);

            e.Graphics.DrawString("Change:", labelFont, Brushes.Black, x, y);
            e.Graphics.DrawString($"${getChange:0.00}", valueFont, Brushes.DarkBlue, x + 100, y);
            y += valueFont.GetHeight(e.Graphics) + spacing;

            // 🕒 Date/Time
            string dateText = DateTime.Now.ToString("MMMM dd, yyyy hh:mm tt");
            e.Graphics.DrawString("🕒 Issued On:", labelFont, Brushes.Black, x, y);
            e.Graphics.DrawString(dateText, italicFont, Brushes.Gray, x + 100, y);
        }

       // private int rowIndex = 0;
        private void printDocument1_BeginPrint(object sender, System.Drawing.Printing.PrintEventArgs e)
        {
           // rowIndex = 0;
        }
    }
}

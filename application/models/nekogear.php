<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Nekogear extends CI_Model{

	function dummy(){
		$this->db->select('SKU, size, color')
	             ->from('order_detail')
	             ->where('order_id',"52F4CC00C32");

	    $query = $this->db->get();
	    if($query->num_rows() > 0){
			return $query->result();
		}
		else{
			return array();
		}

	}

	function dummy_system(){
	    $this->db->select('SKU, size, color')
	             ->from('order_detail')
	             ->where('order_id',"52F4CC00C32");

	    $query = $this->db->get();

	    $skus = array();
		foreach($query->result() as $row):
		    $skus[$row->SKU] = array('sku' => $row->SKU, 'size' => $row->size, 'color' => $row->color);
		endforeach;

	    // 2nd query
	    //foreach($query->result() as $row):
		$return = array();

		foreach($skus as $sku => $data):
		    $SKU = $data['sku'];
		    $size = $data['size'];
		    $color = $data['color'];

	        $this->db->select('stock_quantity')
	                 ->from('item_stock')
	                 ->join('items','items.stock_id = item_stock.stock_id')
	                 ->join('item','item.item_id = items.item_id')
	                 ->where('item.SKU',$SKU)
	                 ->where('item_stock.size',$size)
	                 ->where('item_stock.colour',$color);

	        $query = $this->db->get();
	        $return[] = $query->result();
	    endforeach;

	    if($query->num_rows() > 0){
	    return $return;//$query->result();
	    }
	    else{
	        return array();
	    }
	}

	function update_test($oid){
		// ambil data tabel order_detail
		$this->db->select('SKU,size,color,quantity, category')
				 ->from('order_detail')
				 ->where('order_id',$oid);

		$query = $this->db->get();

		// do update !
		foreach($query->result() as $row):
			$SKU = $row->SKU;
			$size = $row->size;
			$color = $row->color;
			$new_qty = $row->quantity;
			$category = $row->category;

			// ambil stok asli
			$stok = $this->db->select('stock_quantity')
							 ->from('item_stock')
							 ->join('items','items.stock_id = item_stock.stock_id')
							 ->join('item','item.item_id = items.item_id')
							//->set('item_stock.stock_quantity','item_stock.stock_quantity -'.$new_qty)
							 ->where('item_stock.colour',$color)
							 ->where('item_stock.size',$size)
							 ->where('item.SKU',$SKU)
							 ->get()
							 ->row();
					 		//->update('item_stock JOIN items ON items.stock_id = item_stock.stock_id JOIN item ON item.item_id = items.item_id');
			// update kategori pre-order
			//if($new_qty > $stok->stock_quantity){
				// error msg
			if($category == "Pre Order"){
				//echo 'Error?';
				$data['stock_quantity'] = $stok->stock_quantity + $new_qty;
				$this->db->where('item_stock.colour',$color)
						 ->where('item_stock.size',$size)
						 ->where('item.SKU',$SKU)
						 ->update('item_stock JOIN items ON items.stock_id = item_stock.stock_id JOIN item ON item.item_id = items.item_id',$data);
			}else{
			// update kategori ready stock
				$data['stock_quantity'] = $stok->stock_quantity - $new_qty;
				$this->db->where('item_stock.colour',$color)
						 ->where('item_stock.size',$size)
						 ->where('item.SKU',$SKU)
						 ->update('item_stock JOIN items ON items.stock_id = item_stock.stock_id JOIN item ON item.item_id = items.item_id',$data);
			}
		endforeach;
		//return $this->db->last_query();

	}

	function order_info($email){
		$this->db->select('*')
				 ->from('order')
				 ->where('email',$email)
				 ->order_by('order_date','desc')
				 ->group_by('order_id');
		$query = $this->db->get();

		if($query->num_rows() > 0){
			return $query->result();
		}
		else{
			return array();
		}
	}

	function order_detail($oid){
		$this->db->select('*')
				 ->from('order')				 
				 ->join('shipping','shipping.order_id = order.order_id')
				 ->join('payment','payment.order_id = order.order_id')
				 ->join('our_bank_account','our_bank_account.bank_name = payment.bank_destination')
				 ->where('order.order_id',$oid)
				 ->group_by('order.order_id');
		$query = $this->db->get();

		if($query->num_rows() > 0){
			return $query->result();
		}
		else{
			return array();
		}
	}

	function order_details($oid){

		$this->db->select('*')
				 ->from('order_detail')
				 ->join('item','item.SKU = order_detail.SKU')
				 ->where('order_detail.order_id',$oid);
		$query = $this->db->get();

		if($query->num_rows() > 0){
			return $query->result();
		}
		else{
			return array();
		}
	}

	function user_detail($email){
		$this->db->select('*')
				 ->from('users')
				 ->where('email',$email);
		$query = $this->db->get();

		if($query->num_rows() > 0){
			return $query->result();
		}
		else{
			return array();
		}
	}

	function get_cities(){
		$this->db->select('name')
				 ->from('default_cities');
		$query = $this->db->get();

		if($query->num_rows() > 0){
			return $query->result_array();
		}
		else{
			return array();
		}
	}

	function get_our_bank(){
		$this->db->select('*')
				 ->from('our_bank_account');
		$query = $this->db->get();

		if($query->num_rows() > 0){
			return $query->result();
		}
		else{
			return array();
		}
	}

	function all_product(){
		$this->db->select('*')
				 ->from('item')
				 ->where('item.published','Y');
		$query = $this->db->get();

		if($query->num_rows() > 0){
			return $query->result();
		}
		else{
			return array();
		}
	}

	function pre_product(){
		$this->db->select('*')
				 ->from('item')
				 ->where('item.published','Y')
				 ->where('item.category','Pre Order');
		$query = $this->db->get('');

		if($query->num_rows() > 0){
			return $query->result();
		}
		else{
			return array();
		}
	}

	function ready_product(){
		$this->db->select('*')
				 ->from('item')
				 ->where('item.published','Y')
				 ->where('item.category','Ready Stock');
		$query = $this->db->get('');

		if($query->num_rows() > 0){
			return $query->result();
		}
		else{
			return array();
		}
	}

	function info_item($id){
		$this->db->select('*')
				 ->from('item')
				 ->join('category','category.category = item.category')
				 ->where('item.item_id',$id);
		$query = $this->db->get();

		if($query->num_rows() > 0){
			return $query->result();
		}
		else{
			return array();
		}
	}

	function info_colors($id){
		$this->db->select('item_stock.colour')
				 ->from('items')
				 ->join('item_stock','item_stock.stock_id = items.stock_id')
				 ->where('items.item_id',$id)
				 ->group_by('item_stock.colour');
		$query = $this->db->get();
		
		if($query->num_rows() > 0){
			return $query->result();
		}
		else{
			return array();
		}
	}

	function info_sizes(){
		$this->db->select('item_stock.size')
				 ->from('items')
				 ->join('item_stock','item_stock.stock_id = items.stock_id')
				 ->group_by('item_stock.size');
		$query = $this->db->get();
		
		if($query->num_rows() > 0){
			return $query->result();
		}
		else{
			return array();
		}
	}

	function add_validate(){
		$idp = $this->input->post('SKU');
		$idc = $this->input->post('d_color');
		$ids = $this->input->post('d_size');
		$idq = $this->input->post('quantity');

		$this->db->select('item_stock.stock_quantity')
				 ->from('items')
				 ->join('item','item.item_id = items.item_id')
				 ->join('item_stock','item_stock.stock_id = items.stock_id')
				 ->where('item.SKU', $idp)
				 ->where('item_stock.colour', $idc)
				 ->where('item_stock.size', $ids)
				 ->where('item_stock.stock_quantity <',$idq);

		$query = $this->db->get();

		if($query->num_rows() > 0){
			return $query->result();
		}
		else{
			return array();
		}
	}

	function cart_validate(){
		$cart = $this->cart->contents();
		foreach ($cart as $items):
			$SKU	  = $items['name'];
			$size 	  = $items['size'];
			$color 	  = $items['colour'];
			$quantity = $items['qty'];

			$this->db->select('item_stock.stock_quantity')
					 ->from('item')
					 ->join('items','items.item_id = item.item_id')
					 ->join('item_stock','item_stock.stock_id = items.stock_id')
					 ->where('item.SKU',$SKU)
					 ->where('item_stock.colour', $color)
					 ->where('item_stock.size', $size)
					 ->where('item_stock.stock_quantity <',$quantity);

			$query = $this->db->get();
		endforeach;

		if($query->num_rows() > 0){
			return $query->result();
		}
		else{
			return array();
		}	
	}

	function info_checkout(){
		$ionauth =	$this->ion_auth->user()->row();
		$key = uniqid(); // ganti ke caps
		$resi = strtoupper($key);
		$ongkir = $this->cart->total_items()*0.5*10000;

			$this->order_id 	= $resi;
			$this->order_date	= date('Y-m-d H:i:s');
			$this->email		= "$ionauth->email";
			$this->status 		= "PENDING";
			$this->total_bill	= $this->cart->total()+$ongkir;

			$this->db->insert('order', $this);

			$cart = $this->cart->contents();
			foreach($cart as $items):
				$that = new stdClass();

				$that->order_id 	= $resi;
				$that->SKU 			= $items['name'];
				$that->category		= $items['category'];
				$that->weight		= $items['weight']*$items['qty'];
				$that->color		= $items['colour'];
				$that->size			= $items['size'];
				$that->order_price	= $items['subtotal']; //+$items['weight']*$items['qty']*10000;
				$that->quantity		= $items['qty'];

				$this->db->insert('order_detail', $that);
			endforeach;

			// insert shipping
			$their = new stdClass();

			$their->fees		= $ongkir;
			$their->order_id 	= $resi;
			$this->db->insert('shipping', $their);

			// insert payment
			$those = new stdClass();
			$those->order_id 	= $resi;
			$those->status 		= "PENDING";
			$this->db->insert('payment', $those);
		//endforeach;
	}

	function detail_stock($id){
		$this->db->select('item_stock.colour,item_stock.size,item_stock.stock_quantity')
				 ->from('items')
				 ->join('item','item.item_id = items.item_id')
				 ->join('item_stock','item_stock.stock_id = items.stock_id')
				 ->where('item.item_id',$id);

		$query = $this->db->get();

		if($query->num_rows() > 0){
			return $query->result();
		}
		else{
			return array();
		}


		$something = $query->result();

		foreach($something as $colors):
			$ids	= $this->uri->segment(3);
			$warna 	= $colors->colour;

			$this->db->select('item_stock.colour,item_stock.size,item_stock.stock_quantity')
					 ->from('items')
					 ->join('item','item.item_id = items.item_id')
					 ->join('item_stock','item_stock.stock_id = items.stock_id')
					 ->where('item.item_id',$ids)
					 ->where('item_stock.colour',$warna);

			$query = $this->db->get();
		endforeach;

		if($query->num_rows() > 0){
			return $query->result();
		}
		else{
			return array();
		}
	}

	function confirm_payment(){
		$oid = $this->input->post('order_id');
		// set vars
		$account_holder	  = $this->input->post('account_holder');
		$bank_account 	  = $this->input->post('bank_account');
		$bank_origin	  = $this->input->post('bank_origin');
		$bank_destination = $this->input->post('bank_destination');
		$paid_value		  = $this->input->post('paid_value');
		$payment_date 	  = date('Y-m-d H:i:s');
		$payment_method	  = "Transfer";
		$status			  = "LUNAS";

		$this->payment_method 	= $payment_method;
		$this->bank_account 	= $bank_account;
		$this->account_holder	= $account_holder;
		$this->bank_destination	= $bank_destination;
		$this->paid_value		= $paid_value;
		$this->payment_date 	= $payment_date;
		$this->status 			= $status;
		$this->bank_origin		= $bank_origin;
		// update tabel pembayaran
		$this->db->where('order_id',$oid);
		$this->db->update('payment',$this);

		// update tabel order
		$process_status = "PROSES";

		$order = new stdClass();
		$order->status 			= $process_status;
		$order->process_date	= $payment_date;

		$this->db->where('order_id',$oid);
		$this->db->update('order',$order);

		////////////// END OF PAYMENT /////////////
		// ambil data tabel order_detail
		$this->db->select('SKU,size,color,quantity, category')
				 ->from('order_detail')
				 ->where('order_id',$oid);

		$query = $this->db->get();

		// do update !
		foreach($query->result() as $row):
			$SKU = $row->SKU;
			$size = $row->size;
			$color = $row->color;
			$new_qty = $row->quantity;
			$category = $row->category;

			// ambil stok asli
			$stok = $this->db->select('stock_quantity')
							 ->from('item_stock')
							 ->join('items','items.stock_id = item_stock.stock_id')
							 ->join('item','item.item_id = items.item_id')
							//->set('item_stock.stock_quantity','item_stock.stock_quantity -'.$new_qty)
							 ->where('item_stock.colour',$color)
							 ->where('item_stock.size',$size)
							 ->where('item.SKU',$SKU)
							 ->get()
							 ->row();
					 		//->update('item_stock JOIN items ON items.stock_id = item_stock.stock_id JOIN item ON item.item_id = items.item_id');
			// update kategori pre-order
			//if($new_qty > $stok->stock_quantity){
				// error msg
			if($category == "Pre Order"){
				//echo 'Error?';
				$data['stock_quantity'] = $stok->stock_quantity + $new_qty;
				$this->db->where('item_stock.colour',$color)
						 ->where('item_stock.size',$size)
						 ->where('item.SKU',$SKU)
						 ->update('item_stock JOIN items ON items.stock_id = item_stock.stock_id JOIN item ON item.item_id = items.item_id',$data);
			}else{
			// update kategori ready stock
				$data['stock_quantity'] = $stok->stock_quantity - $new_qty;
				$this->db->where('item_stock.colour',$color)
						 ->where('item_stock.size',$size)
						 ->where('item.SKU',$SKU)
						 ->update('item_stock JOIN items ON items.stock_id = item_stock.stock_id JOIN item ON item.item_id = items.item_id',$data);
			}
		endforeach;
		//return $this->db->last_query();
	}

	function confirm_delete(){
		$oid = $this->uri->segment(3);

		// delete dari tabel payment
		$this->db->delete('payment',array('order_id'=>$oid));

		// delete dari tabel order
		$this->db->delete('order',array('order_id'=>$oid));

		// delete dari tabel order_detail (foreach)
			$this->db->delete('order_detail',array('order_id'=>$oid));

		// delete dari tabel shipping
		$this->db->delete('shipping',array('order_id'=>$oid));
	}

	function payment_validate(){
		$oid = $this->uri->segment(3);
		$paid = $this->input->post('paid_value');

		$this->db->select('*')
				 ->from('order')
				 ->join('payment','payment.order_id = order.order_id')
				 ->where('order.order_id',$oid)
				 ->where('order.total_bill >', $paid);

		$query = $this->db->get();

		if($query->num_rows() > 0){
			return $query->result();
		}
		else{
			return array();
		}	
	}
}
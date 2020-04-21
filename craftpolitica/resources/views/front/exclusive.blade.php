@extends('layouts.layout')

@section('container')

    <div class="container ">
        <ul class="breadcrumb bg-white">
            <li class="breadcrumb-item"><a href="#">home</a></li>
            <li class="breadcrumb-item active">Catagory1</li>
        </ul>

        <div class="row">

            <!--        form two columns for news -->
            <section class="col-md-8 col-12  ">
                <cite> {{$posts['author']}}</cite>
                <div  class="d-flex">
                    <time  class="mr-auto" >Updated at :  June 25, 2019 16:18 IST</time>
                    <figure>
                        <a href="#"><img style="width: 50px;height: 50px;" src="img/fb.png"> </a>
                        <a href="#"><img style="width: 50px;height: 50px;" src="img/twitter.png"> </a>
                        <a href="#"><img style="width: 50px;height: 50px;" src="img/youtube.png"> </a>
                    </figure>

                </div>
                <hr/>
                <article >


                    <?php echo $data; ?>
                </article>

                <!--        Enter your comment here-->

                <div class="card">
                    <div class="card-header h5"> Leave Your Comment here..</div>
                    <div class="card-body">

                        <form>

                            <div class="form-group">
                                <label for="name">Full Name</label>
                                <input type="email" class="form-control" id="name" aria-describedby="emailHelp" placeholder="Enter Full Name">
                            </div>
                            <div class="form-group">
                                <label for="exampleInputEmail1">Email address</label>
                                <input type="email" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Enter email">
                            </div>

                            <div class="form-group">
                                <label for="comment">Comment</label>
                                <textarea class="form-control" id="comment" rows="3" placeholder="Type your comment..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </form>
                    </div>

                </div>



                <!--        Show your comments here-->


                <section class="card">
                    <div class="card-header"> Comments:</div>
                    <ul class="list-unstyled ">
                        <li class="media m-3">
                            <i class="fas fa-user-circle h3 m-2"></i>
                            <div class="media-body">
                                <h6 class="">Sonu Kumar</h6>
                                <small class="text-muted"> 7/1/2019 6:25pm</small>

                                <p>

                                    Cras sit amet nibh libero, in gravida nulla. Nulla vel metus scelerisque ante sollicitudin. Cras purus odio, vestibulum in vulputate at, tempus viverra turpis. Fusce condimentum nunc ac nisi vulputate fringilla. Donec lacinia congue felis in faucibus.

                                </p>

                            </div>
                        </li>
                        <div class="dropdown-divider"></div>
                        <li class="media m-3">
                            <i class="fas fa-user-circle h3 m-2"></i>
                            <div class="media-body">
                                <h6 class="">Sonu Kumar</h6>
                                <small class="text-muted"> 7/1/2019 6:25pm</small>

                                <p>

                                    Cras sit amet nibh libero, in gravida nulla. Nulla vel metus scelerisque ante sollicitudin. Cras purus odio, vestibulum in vulputate at, tempus viverra turpis. Fusce condimentum nunc ac nisi vulputate fringilla. Donec lacinia congue felis in faucibus.

                                </p>

                            </div>
                        </li>
                    </ul>

                </section>
            </section>



            <aside  class="col-md-4 col-12 ">

                <div class="jumbotron text-center h-25">
                    Advertisement
                </div>


                <div class="list-group">
                    <a href="#" class="list-group-item list-group-item-action ">
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1">List group item heading</h5>
                            <small>3 days ago</small>
                        </div>
                        <p class="mb-1">Donec id elit non mi porta gravida at eget metus. Maecenas sed diam eget risus varius blandit.</p>
                        <small>Donec id elit non mi porta.</small>
                    </a>
                    <a href="#" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1">List group item heading</h5>
                            <small class="text-muted">3 days ago</small>
                        </div>
                        <p class="mb-1">Donec id elit non mi porta gravida at eget metus. Maecenas sed diam eget risus varius blandit.</p>
                        <small class="text-muted">Donec id elit non mi porta.</small>
                    </a>
                    <a href="#" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1">List group item heading</h5>
                            <small class="text-muted">3 days ago</small>
                        </div>
                        <p class="mb-1">Donec id elit non mi porta gravida at eget metus. Maecenas sed diam eget risus varius blandit.</p>
                        <small class="text-muted">Donec id elit non mi porta.</small>
                    </a>
                </div>
            </aside>
        </div>
    </div>


@endsection
